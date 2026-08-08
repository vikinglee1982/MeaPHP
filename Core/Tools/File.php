<?php

namespace MeaPHP\Core\Tools;

use MeaPHP\Core\Reply\Reply;
use MeaPHP\Core\Utils\Path;

/**
 * ⚠️ 职责说明：本类仅处理【文件】操作。
 * 目录相关操作（创建/删除/列出）请使用 MeaPHP\Core\Tools\Directory
 */
class File
{
    /**
     * @var self|null 单例对象实例
     */
    private static $obj = null;

    private function __clone() {}
    private function __construct() {}

    public static function active(): self
    {
        if (!self::$obj instanceof self) {
            self::$obj = new self();
        }
        return self::$obj;
    }

    /**
     * 保存文件（私有）
     */
    private function save(string $src, array $file): array
    {
        if (file_exists($src)) {
            return Reply::To('err', '文件已存在', ['src' => $src]);
        }

        if (rename($file['tmp_name'], $src)) {
            try {
                $url = Path::toUrlPath($src);
                return Reply::To('ok', '上传成功', [
                    'path' => $url,
                    'src' => $src,
                ]);
            } catch (\Exception $e) {
                return Reply::To('ok', '上传成功（URL 生成失败）', ['src' => $src]);
            }
        }

        return Reply::To('err', '储存失败', ['src' => $src]);
    }

    /**
     * 解析文件路径信息
     */
    public function parsePath(string $path): array
    {
        $localPath = Path::toLocalPath($path);
        if (!Path::isUnderRoot($localPath)) {
            return Reply::To('err', '路径不安全或超出根目录');
        }

        if (!file_exists($localPath)) {
            return Reply::To('err', '文件不存在');
        }

        return Reply::To('ok', '文件存在', [
            'localPath' => $localPath,
            'fileFullName' => basename($localPath),
            'fileName' => pathinfo($localPath, PATHINFO_FILENAME),
            'fileType' => pathinfo($localPath, PATHINFO_EXTENSION),
            'fileSize' => filesize($localPath),
            'fileMd5' => md5_file($localPath),
            'fileMime' => mime_content_type($localPath),
        ]);
    }

    /**
     * 本地文件转 URL
     */
    public function localFileToUrl(string $localPath, bool $exists = true): array
    {
        if (!Path::isUnderRoot($localPath)) {
            return Reply::To('err', '路径不安全');
        }
        $localPath = Path::toLocalPath($localPath);
        if ($exists && !file_exists($localPath)) {
            return Reply::To('err', '文件不存在', ['localPath' => $localPath]);
        }

        try {
            $urlPath = Path::toUrlPath($localPath);
            $protocol = (
                (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            ) ? 'https://' : 'http://';

            $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
            $url = $protocol . $host . $urlPath;
            return Reply::To('ok', $exists ? '文件存在' : 'URL 生成成功', ['url' => $url]);
        } catch (\Exception $e) {
            return Reply::To('err', 'URL 生成失败: ' . $e->getMessage());
        }
    }

    /**
     * 单文件移动
     */
    public function MoveMonofile(?string $oldName, ?string $newName, bool $mkdir = false): array
    {
        if (!$oldName) return Reply::To('err', '缺少 oldName');
        if (!$newName) return Reply::To('err', '缺少 newName');

        $oldPath = Path::toLocalPath($oldName);
        $newPath = Path::toLocalPath($newName);

        if (!is_file($oldPath)) {
            return Reply::To('err', '源文件不存在: ' . $oldName);
        }

        if (!Path::isUnderRoot($oldPath) || !Path::isUnderRoot($newPath)) {
            return Reply::To('err', '路径不安全');
        }

        // 自动创建目标目录（委托给 Directory）
        if ($mkdir) {
            $dirRes = Directory::active()->create(dirname($newName));
            if ($dirRes['sc'] !== 'ok') {
                return $dirRes;
            }
        } else {
            $targetDir = dirname(Path::toLocalPath($newName));
            if (!is_dir($targetDir)) {
                return Reply::To('err', '目标目录不存在且未启用自动创建: ' . $targetDir);
            }
        }

        if (rename($oldPath, $newPath)) {
            try {
                $relPath = Path::toUrlPath($newPath);
                return Reply::To('ok', '移动成功', ['path' => $relPath]);
            } catch (\Exception $e) {
                return Reply::To('ok', '移动成功（URL 获取失败）', ['src' => $newPath]);
            }
        }

        return Reply::To('err', '移动失败');
    }

    /**
     * 多文件移动
     */
    public function MoveMultifile(array $arr, bool $mkdir = false): array
    {
        if (!is_array($arr) || empty($arr)) {
            return Reply::To('err', '参数必须是非空数组');
        }

        foreach ($arr as $item) {
            if (!isset($item['oldName']) || !isset($item['newName'])) {
                return Reply::To('err', '数组项必须包含 oldName 和 newName');
            }
        }

        $results = [];
        foreach ($arr as $item) {
            $res = $this->MoveMonofile($item['oldName'], $item['newName'], $mkdir);
            if ($res['sc'] !== 'ok') {
                return $res;
            }
            $results[] = $res['data']['path'] ?? $res['data']['src'];
        }

        return Reply::To('ok', '批量移动成功', $results);
    }

    /**
     * 单文件复制
     */
    public function CopyMonofile(string $oldFilePath, string $newFilePath, bool $mkdir = false): array
    {
        $oldPath = Path::toLocalPath($oldFilePath);
        $newPath = Path::toLocalPath($newFilePath);

        if (!is_file($oldPath)) {
            return Reply::To('err', '源文件不存在');
        }

        if (!Path::isUnderRoot($oldPath) || !Path::isUnderRoot($newPath)) {
            return Reply::To('err', '路径不安全');
        }

        if ($mkdir) {
            $dirRes = Directory::active()->create(dirname($newFilePath));
            if ($dirRes['sc'] !== 'ok') {
                return $dirRes;
            }
        } else {
            $targetDir = dirname(Path::toLocalPath($newFilePath));
            if (!is_dir($targetDir)) {
                return Reply::To('err', '目标目录不存在且未启用自动创建');
            }
        }

        if (copy($oldPath, $newPath)) {
            try {
                $relPath = Path::toUrlPath($newPath);
                return Reply::To('ok', '复制成功', ['path' => $relPath]);
            } catch (\Exception $e) {
                return Reply::To('ok', '复制成功（URL 获取失败）', ['src' => $newPath]);
            }
        }

        return Reply::To('err', '复制失败');
    }

    /**
     * 多文件复制
     */
    public function copyMultifile(array $arr, bool $mkdir = false): array
    {
        if (!is_array($arr) || empty($arr)) {
            return Reply::To('err', '参数必须是非空数组');
        }

        foreach ($arr as $item) {
            if (!isset($item['oldName']) || !isset($item['newName'])) {
                return Reply::To('err', '数组项必须包含 oldName 和 newName');
            }
        }

        $results = [];
        foreach ($arr as $item) {
            $res = $this->CopyMonofile($item['oldName'], $item['newName'], $mkdir);
            if ($res['sc'] !== 'ok') {
                return $res;
            }
            $results[] = $res['data']['path'] ?? $res['data']['src'];
        }

        return Reply::To('ok', '批量复制成功', $results);
    }

    /**
     * 生成唯一文件名
     */
    private function createFileName(array $file): string
    {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (!$ext) {
            $ext = 'bin';
        }
        return bin2hex(random_bytes(16)) . '.' . strtolower($ext);
    }

    /**
     * 保存图片（单张）
     */
    public function saveImage(?array $file, ?string $folderName, ?string $fileName = null): array
    {
        if (!$file) {
            return Reply::To('err', '缺少上传文件');
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes, true)) {
            return Reply::To('err', '仅支持 JPG/PNG/WEBP/GIF 图片', ['type' => $file['type']]);
        }

        if (!$folderName) {
            return Reply::To('err', '缺少保存目录');
        }

        if (strpos($folderName, 'undefined') !== false || strpos($folderName, 'null') !== false) {
            return Reply::To('err', '目录名不能包含 "undefined" 或 "null"');
        }

        // 委托 Directory 创建目录（这是文件保存的必要前提）
        $dirRes = Directory::active()->create($folderName);
        if ($dirRes['sc'] !== 'ok') {
            return $dirRes;
        }

        if (!$fileName) {
            $fileName = $this->createFileName($file);
        }

        $userDir = Path::toLocalPath($folderName);
        $src = rtrim($userDir, '/') . '/' . $fileName;
        return $this->save($src, $file);
    }

    /**
     * 删除单个或多个文件
     */
    public function delFile($pathData): array
    {
        $paths = [];

        if (is_string($pathData)) {
            $paths = [Path::toLocalPath($pathData)];
        } elseif (is_array($pathData)) {
            if (empty($pathData)) {
                return Reply::To('err', '路径数组为空');
            }
            foreach ($pathData as $p) {
                $paths[] = Path::toLocalPath($p);
            }
        } else {
            return Reply::To('err', '参数必须是字符串或数组');
        }

        foreach ($paths as $p) {
            if (!Path::isUnderRoot($p)) {
                return Reply::To('err', '路径不安全: ' . $p);
            }
            if (!file_exists($p)) {
                return Reply::To('err', '文件不存在: ' . $p);
            }
            if (!is_file($p)) {
                return Reply::To('err', '路径不是文件: ' . $p);
            }
        }

        $count = 0;
        foreach ($paths as $p) {
            if (unlink($p)) {
                $count++;
            } else {
                return Reply::To('err', '删除失败: ' . $p);
            }
        }

        return Reply::To('ok', '成功删除 ' . $count . ' 个文件', ['total' => $count]);
    }

    /**
     * 检查文件大小（单位 MB）
     */
    public function checkFileSize(array $file, int $maxSize = 50): array
    {
        $maxBytes = $maxSize * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            return Reply::To('error', "文件大小超出 {$maxSize}MB 限制", [
                'fileSize' => $file['size'],
                'maxSizeBytes' => $maxBytes
            ]);
        }
        return Reply::To('ok', "文件大小符合要求（≤{$maxSize}MB）", ['fileSize' => $file['size']]);
    }

    /**
     * 保存文档类文件（非图片）
     */
    public function saveDoc(array $file, string $path): array
    {
        // 委托 Directory 创建目录
        $dirRes = Directory::active()->create($path);
        if ($dirRes['sc'] !== 'ok') {
            return $dirRes;
        }

        $fileName = $this->createFileName($file);
        $userDir = Path::toLocalPath($path);
        $src = rtrim($userDir, '/') . '/' . $fileName;
        return $this->save($src, $file);
    }
}
