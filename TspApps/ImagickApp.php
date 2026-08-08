<?php
/*
 * @Author: vikinglee1982 87834084@qq.com
 * @Date: 2024-09-13 21:53:40
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2024-09-14 09:53:12
 * @FilePath: \工作台\Servers\huayun_server\MeaPHP\TspApps\Imagick.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace MeaPHP\TspApps;

use MeaPHP\Core\Reply\Reply;
use MeaPHP\Core\Tools\File;

class ImagickApp
{
    private static $instance = null;
    private $imagick;

    // 阻止外部克隆此工具类实例
    private function __clone() {}

    // 私有化构造方法初始化，禁止外部使用
    private function __construct()
    {
        // $this->imagick = new \Imagick();
        if (class_exists('\Imagick')) {
            $this->imagick = new \Imagick();
        } else {
            throw new \Exception('Imagick extension is not available. Please install and enable the Imagick extension.');
        }
    }

    // 内部产生静态对象
    public static function active()
    { // echo "建立了";
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    /**
     * 将PDF文件转换为图片
     * @param string $path PDF文件路径
     * @param int $imgWidth 图片宽度
     * @param int $imgHeight 图片高度
     * @param string $format 图片格式
     * @param int [停用状态] $quality 压缩质量1-100
     * @return array 返回操作结果
     */
    public function pdfToImg(
        string $path,
        int $imgWidth = 0,
        int $imgHeight = 0,
        string $destFile = null,
        $format = 'Webp'
        // int $quality = 100
    ): array {
        if ($imgWidth == 0 && $imgHeight == 0) {
            return Reply::To('err', '图片宽度和高度不能同时为0');
        }
        $File = File::active();
        $fileRes = $File->parsePath($path);

        if ($fileRes['sc'] != 'ok') {
            return Reply::To('err', '文件不存在');
        }
        if ($fileRes['data']['fileType'] != 'pdf') {
            return Reply::To('err', '文件不是pdf文件');
        }

        $localPath = $fileRes['data']['localPath'];
        $outputDir = $destFile ?? dirname($localPath);

        //验证outputDir是否有这个文件夹
        if (!is_dir($outputDir)) {
            return Reply::To('err', '输出目录不存在');
        }
        try {
            // 读取PDF文件
            $this->imagick->readImage($localPath);
            $imagesCount = $this->imagick->getNumberImages(); // 获取PDF中的页数
            $imgPaths = [];

            for ($i = 0; $i < $imagesCount; $i++) {
                $this->imagick->setIteratorIndex($i); // 设置当前页码

                if ($imgWidth !== 0 && $imgHeight !== 0) {
                    // 如果指定了宽度和高度，则调整图像尺寸
                    $this->imagick->resizeImage($imgWidth, $imgHeight, \Imagick::FILTER_LANCZOS, 1);
                } elseif ($imgWidth !== 0) {
                    // 如果只指定了宽度，保持原高宽比调整图像
                    $this->imagick->thumbnailImage($imgWidth, 0);
                } elseif ($imgHeight !== 0) {
                    // 如果只指定了高度，保持原高宽比调整图像
                    $this->imagick->thumbnailImage(0, $imgHeight);
                }
                // if ($format == 'webp') {
                //     $this->imagick->setImageFormat('webp-lossless'); // 使用无损压缩模式
                // } else {
                $this->imagick->setImageFormat($format); // 设置输出格式
                // }
                // 设置压缩质量
                // $this->imagick->setImageCompressionQuality($quality);
                $this->imagick->writeImage("$outputDir/page_$i.$format"); // 保存文件
                $imgPaths[] = "$outputDir/page_$i.$format";
            }

            return Reply::To('ok', '生成图片成功', [
                'outputPaths' => $imgPaths,
            ]);
            // 'fileRes' => $fileRes,
            // 'localPath' => $localPath,
            // 'imagesCount' => $imagesCount,
            // 'outputDir' => $outputDir,
            // 'dir($localPath)' => dirname($localPath),
            // '支持格式' => $this->imagick->queryFormats(),
        } catch (\Exception $e) {
            return Reply::To('err', 'Imagick类加载失败或者生成图片失败', [$e->getMessage()]);
        }
    }
    //析构函数
    public function __destruct()
    {
        $this->imagick->clear();
        $this->imagick->destroy();
    }
}
