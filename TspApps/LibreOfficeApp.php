<?php

namespace MeaPHP\TspApps;

use MeaPHP\Core\Tools\File;
use MeaPHP\Core\Reply\Reply;

class LibreOfficeApp
{
    private static $instance = null;
    private $libreofficePath = '/opt/libreoffice24.2/program/soffice.bin';
    // private $defaultFont = 'WenQuanYi Micro Hei Mono'; // 默认字体
    private $defaultFont = 'Noto Sans SC'; // 默认字体改为中文简体
    private $fontMsg = []; // 字体信息记录
    private $tempDir = null; // 临时目录

    private function __clone() {}

    private function __construct()
    {
        // 确保 LibreOffice 路径正确
        if (!file_exists($this->libreofficePath)) {
            return Reply::To('err', '没有找到LibreOffice');
        }
    }

    public static function active()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function DocxToPdf(string $path, string $outputDir = null)
    {
        $File = File::active();
        $fileRes = $File->parsePath($path);

        if ($fileRes['sc'] != 'ok') {
            return Reply::To('err', '文件不存在');
        }
        $fileType = $fileRes['data']['fileType'];
        if ($fileType != 'docx' && $fileType != 'doc') {
            return Reply::To('err', '文件不是docx或者doc格式', [$fileRes, $fileType]);
        }
        $localPath = $fileRes['data']['localPath'];
        if (!file_exists($localPath) || !is_readable($localPath)) {
            return Reply::To('err', '源文件不存在或无法读取', [
                'localPath' => $localPath,
            ]);
        }
        $targetDir = $outputDir ?? dirname($localPath);
        if (!is_dir($targetDir)) {
            return Reply::To('err', '输出目录不存在');
        }

        // 检查文件和目录权限
        if (!is_readable($localPath) || !is_writable($targetDir)) {
            return Reply::To('err', '文件不可读或目录不可写');
        }

        // 设置环境变量，确保使用正确的字符集
        putenv('HOME=/tmp/');
        putenv('LC_ALL=zh_CN.UTF-8'); // 使用 UTF-8 编码的中文环境
        putenv('LANG=zh_CN.UTF-8'); // 使用 UTF-8 编码的中文环境

        // 创建临时目录
        $tempDir = $this->createTempDirectory($localPath);

        // 替换文档中的所有字体
        $this->replaceMissingFonts($localPath, $tempDir);

        // 构建 LibreOffice 命令行参数
        $pdfFileName = basename($localPath, $fileType) . 'pdf';
        $targetFilePath = $targetDir . '/' . $pdfFileName;
        $arguments = [
            '--headless',
            '--convert-to',
            'pdf',
            '--outdir',
            escapeshellarg($targetDir),
            escapeshellarg($localPath)
        ];

        // 组装命令
        $command = 'LC_ALL=zh_CN.UTF-8 LANG=zh_CN.UTF-8 ' . escapeshellcmd($this->libreofficePath) . ' ' . implode(' ', $arguments);

        try {
            // 执行命令，并捕获输出
            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            // 检查执行结果
            if ($returnVar === 0) {
                if (file_exists($targetFilePath) && is_readable($targetFilePath)) {
                    return Reply::To('ok', 'PDF文件已生成', [
                        'pdf' => $targetFilePath,
                        'docx' => $localPath,
                        // 'fontMsg' => $this->fontMsg,
                        // 'command' => $command
                    ]);
                } else {
                    return Reply::To('err', '生成的PDF文件不存在或无法读取', [
                        'pdf' => $targetFilePath,
                        'fileRes' => $fileRes,
                        // 'targetDir' => $targetDir,
                        // 'pdfFileName' => $pdfFileName,
                        // 'localPath' => $localPath,
                        // 'output' => $output,
                        // // 'command' => $command,
                        // 'returnVar' => $returnVar,
                    ]);
                }
            } else {
                return Reply::To('err', '转换失败', [
                    // 'output' => $output,
                    // 'command' => $command,
                    // 'returnVar' => $returnVar,
                ]);
            }
        } catch (\Exception $e) {
            return Reply::To('err', '转换过程中发生错误', [
                'message' => $e->getMessage(),
                // 'command' => $command,
            ]);
        }

        // 清理临时文件
        $this->deleteRecursive($tempDir);
    }

    private function createTempDirectory($localPath)
    {
        if ($this->tempDir === null) {
            // 创建临时目录的路径
            $tempDirBase = sys_get_temp_dir() . '/docx_temp_' . md5($localPath);
            $tempDir = $tempDirBase;

            // 如果临时目录已存在，则在后面添加数字后缀直到找到不存在的目录
            $i = 1;
            while (is_dir($tempDir)) {
                $tempDir = $tempDirBase . '_' . $i++;
            }

            // 创建临时目录
            mkdir($tempDir, 0777, true);
            $this->tempDir = $tempDir;
        }

        return $this->tempDir;
    }

    private function replaceMissingFonts($localPath, $tempDir)
    {
        // 创建临时目录
        if (!file_exists($tempDir)) {
            mkdir($tempDir);
        }

        // 解压 .docx 文件到临时目录
        system("unzip -o -q $localPath -d $tempDir > /dev/null 2>&1");

        // 替换所有缺失的字体
        $documentXmlPath = $tempDir . '/word/document.xml';
        $documentXml = file_get_contents($documentXmlPath);

        // 检查字体是否已安装
        $documentXml = preg_replace_callback(
            '/(<w:rFonts[^>]*)(w:[^=]*="[^"]*")(.*>)/',
            function ($matches) {
                // 获取字体名称
                preg_match('/w:(?:ascii|eastAsia|hAnsi|cs)="([^"]*)"/', $matches[0], $fontMatch);
                $fontName = isset($fontMatch[1]) ? trim($fontMatch[1], '"') : '';

                // 如果字体未安装，则替换为默认字体
                if (!$this->isFontInstalled($fontName)) {
                    // 替换字体属性
                    $matches[0] = str_replace($fontName, $this->defaultFont, $matches[0]);
                }

                return $matches[0];
            },
            $documentXml
        );

        // 确保文档 XML 文件使用 UTF-8 编码保存
        file_put_contents($documentXmlPath, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" . $documentXml);

        // 记录替换后的 XML 文件内容
        $this->fontMsg['afterReplacement'] = file_get_contents($documentXmlPath);

        // 重新打包 .docx 文件
        $newDocxPath = $tempDir . '/modified.docx';
        system("cd $tempDir && zip -r ../$newDocxPath * > /dev/null 2>&1");

        $this->fontMsg['newDocxPath'] = $newDocxPath;

        // 移动修改后的 .docx 文件到原位置
        rename($newDocxPath, $localPath);
    }

    private function isFontInstalled($fontName)
    {
        $fontsList = shell_exec('fc-list :family');
        $this->fontMsg['fontsList'] = $fontsList;
        $fontsArray = explode("\n", $fontsList);
        foreach ($fontsArray as $fontInfo) {
            if (strpos($fontInfo, $fontName) !== false) {
                return true;
            }
        }
        return false;
    }

    private function deleteRecursive($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        $this->deleteRecursive($dir . "/" . $object); // Recurse into subdirectory
                    } else {
                        unlink($dir . "/" . $object); // Delete file
                    }
                }
            }
            reset($objects);
            rmdir($dir); // Delete directory
        } else {
            unlink($dir); // Delete file
        }
    }
}
