<?php

namespace MeaPHP\Core\Tools;

/**
 * 文件上传，接受保存到服务器
@入参{
$phone,
$fileName,
$basicsPath
}
 */

class Save
{

    private static $obj = null;
    private $folderName;

    private $fileType;

    //阻止外部克隆书库工具类
    private function __clone()
    {
    }

    //私有化构造方法初始化，禁止外部使用
    private function __construct()
    {
    }
    //内部产生静态对象
    public static function active()
    {
        // var_dump($dbkey);
        if (!self::$obj instanceof self) {
            //如果不存在，创建保存
            self::$obj = new self();
        }
        return self::$obj;
    }
    //生成毫秒级时间戳
    public function image($file, $folderName, $fileName = null)
    {
        //判断入参的文件类型，必须时图片格式
        $this->fileType = exif_imagetype($file);
        if (!$file) {
            return "error:请缺少入参image文件";
        } elseif ($this->fileType != IMAGETYPE_GIF && $this->fileType != IMAGETYPE_JPEG && $this->fileType != IMAGETYPE_PNG && $this->fileType != IMAGETYPE_BMP) {
            return "error:文件类型支持[gif/jpg/jpge/png/bmp]";
        } elseif (!$folderName) {
            return "error:缺少文件夹目录（项目根路径下的目录）";
        } else {
            $this->folderName = $folderName;

            $this->file       = $file;

            if (!$fileName) {
                //如果用户没有传进文件名，就生成一个，不重复的文件名
                $fileName = $this->createFileName();
            }

            //合成需要存放文件的路径
            // $userDir = iconv('utf-8', 'gbk', $this->basicsPath . '/' . $folderName . '/' . $fileType);

            $userDir = iconv('utf-8', 'gbk', $_SERVER['DOCUMENT_ROOT'] . '/' . $folderName);


            if (!is_dir($userDir)) {

                mkdir(iconv("UTF-8", "GBK", $userDir), 0777, true);
            }
            //开始储存

            $src = $userDir . '/' . $fileName;

            if (move_uploaded_file($file['tmp_name'], $src)) {
                //储存完成合成路径返回

                //
                // $url = str_replace($_SERVER['DOCUMENT_ROOT'], $_SERVER['HTTP_HOST'], $src);
                $url = str_replace($_SERVER['DOCUMENT_ROOT'], '', $src);

                return $url;
            } else {
                //储存失败
                $data['src'] = $src;
                // return 3000;
                return $src;
            }
        }
    }

    private function createFileName()
    {

        //获取文件后缀名
        $extension = substr(strrchr($this->file['name'], '.'), 1);
        // md5(uniqid(md5(microtime(true)), true));
        return md5($this->folderName . md5(uniqid(md5(microtime(true)), true)) . $this->fileType) . '.' . $extension;
    }
}
