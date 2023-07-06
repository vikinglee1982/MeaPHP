<?php

/**
 * 读取文件时生成缩略图///还未整理
@入参{
$phone,
$fileName,
$basicsPath
}

需要生成图片的模式
$model:

1：(入参中只有宽数值)以宽为准，高度自动缩放
2：(入参中只有高数值)以高为准，宽度自动缩放
3:(入参中宽、高都有数值)固定宽高，将图片等比放入

$vh:
v=横向标准
h=纵向标准
$fillColor:填充颜色

$res_t:文件类型
1 = GIF，2 = JPG，3 = PNG，4 = SWF，5 = PSD，6 = BMP，7 = TIFF(intel byte order)，8 = TIFF(motorola byte order)，9 = JPC，10 = JP2，11 = JPX，12 = JB2，13 = SWC，14 = IFF，15 = WBMP，16 = XBM
 *
 */
class Fotophire
{

    private static $obj = null;

    //入参资源的宽度;
    private $res_w = null;
    //入参资源的高度
    private $res_h = null;
    //入参资源的类型（目前支持jpg/jpeg;png;gif）
    private $res_t = null;
    //入参资源的资源生成
    private $res = null;

    //入参资源路径
    private $fileImage = null;

    //需要保存的资源路径
    private $savePath = null;

    //内部产生静态对象
    public static function start($dbkey)
    {
        //var_dump($dbkey);
        if (!self::$obj instanceof self) {
            //如果不存在，创建保存
            self::$obj = new self($dbkey);
        }
        return self::$obj;
    }

    //阻止外部克隆书库工具类
    private function __clone()
    {
    }

    //私有化构造方法初始化，禁止外部使用
    private function __construct($dbkey)
    {

        // $this->online  = $dbkey['online'];
        // $this->safeUrl = $dbkey['url'];

    }

    //主入口
    public function thumb($file, $path, $get_w = 0, $get_h = 0, $https = true)
    {
        if ($get_w == 0 && $get_h == 0) {
            // throw new Exception("至少有一个宽高尺寸");
            return '至少有一个宽高尺寸';
        }

        //入参资源路径和保存路径整理
        $path = $this->amendPath($file, $path);

        //以前有这个文件，直接返回对应的路径
        if (file_exists($this->savePath)) {

            if ($https) {
                return 'https://' . $_SERVER['SERVER_NAME'] . str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->savePath);
            } else {
                return 'http://' . $_SERVER['SERVER_NAME'] . str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->savePath);
            }
        } else {
            //没有开始生成

            // var_dump($this->fileImage);
            // var_dump($this->savePath);

            // return $this->fileImage;
            //入参资源判定整理
            $this->resourceHandler($this->fileImage);
            //获取画布的尺寸（需求尺寸）
            $canvasZize = $this->size($get_w, $get_h);
            //建立画布
            // var_dump($canvasZize);

            $canvas = imagecreatetruecolor($canvasZize['c_w'], $canvasZize['c_h']);

            //如果图片的类型是png，填充颜色无效，需要保持透明度

            if ($this->res_t == 3) {
                // 给画布上底色
                $back = imagecolorallocate($canvas, 0, 0, 0);
                //填充颜色，
                imagefill($canvas, 0, 0, $back);
                //将底色设置透明（设置颜色代号，在设置透明）
                imagecolortransparent($canvas, $back);
            }

            //复制拷贝图像

            imagecopyresampled($canvas, $this->res, 0, 0, 0, 0, $canvasZize['c_w'], $canvasZize['c_h'], $canvasZize['r_w'], $canvasZize['r_h']);

            // var_dump($modeInfo);

            $this->save($canvas, $this->savePath);
            if (file_exists($this->savePath)) {

                if ($https) {
                    return 'https://' . $_SERVER['SERVER_NAME'] . str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->savePath);
                } else {
                    return 'http://' . $_SERVER['SERVER_NAME'] . str_replace($_SERVER['DOCUMENT_ROOT'], '', $this->savePath);
                }
            } else {
                return 'fail';
            }
        }
    }
    //资源路径和储存路径整理
    private function amendPath($fileImage, $path)
    {

        //判断入参是绝对路径还是相对路径；输出绝对路径

        if (strpos($fileImage, $_SERVER['DOCUMENT_ROOT']) === false) {
            //入参是没有根目录的相对路径（不包含根目录）

            $this->fileImage = $_SERVER['DOCUMENT_ROOT'] . $fileImage;
        } else {

            $this->fileImage = $fileImage;
            // return '有根目录';
        }
        if (strpos($path, $_SERVER['DOCUMENT_ROOT']) === false) {
            //入参是没有根目录的相对路径（不包含根目录）

            $this->savePath = $_SERVER['DOCUMENT_ROOT'] . $path;
        } else {

            $this->savePath = $path;
        }

        $savePathInfo = pathinfo($this->savePath);

        //var_dump($savePathInfo);

        if (!file_exists($savePathInfo['dirname'])) {
            mkdir($savePathInfo['dirname'], 0777, true);
        }
    }
    //文件保存
    private function save($file, $savePath)
    {
        if ($this->res_t == 1) {
            //gif文件保存
            imagegif($file, $savePath);
        } elseif ($this->res_t == 2) {
            // jpeg文件保存
            imagejpeg($file, $savePath);
        } elseif ($this->res_t == 3) {
            // png文件保存
            imagepng($file, $savePath);
        } else {
            //异常
            throw new Exception("资源保存失败;");
        }

        //销毁资源
        imagedestroy($file);
    }
    //入参资源判定并生成资源
    private function resourceHandler($fileImage)
    {

        if (!is_file($fileImage) || getimagesize($fileImage) === false) {
            // 判断文件是否存在或者不是图片文件.
            throw new Exception("入参的资源不是存在或者不是图片类型");
        } else {
            $fileInfo = getimagesize($fileImage);

            $this->res_w = $fileInfo[0];
            $this->res_h = $fileInfo[1];
            $this->res_t = $fileInfo[2];

            if ($this->res_t == 1) {
                //图片时gif格式

                $this->res = imagecreatefromgif($fileImage);
            } elseif ($this->res_t == 2) {
                //图片是jpg格式

                $this->res = imagecreatefromjpeg($fileImage);
            } elseif ($this->res_t == 3) {
                //图片是png格式

                $png = imagecreatefrompng($fileImage);
                imagesavealpha($png, true);

                $this->res = $png;
                //设置标记以在保存 PNG 图像时保存完整的 alpha 通道信息。

            } else {
                throw new Exception("图片资源只支持jpg/jpge、png和gif文件");
            }
        }
    }
    //图片缩放尺寸模式判定
    private function size($get_w, $get_h)
    {

        if ($get_w > 0 && $get_h == 0) {
            //宽度固定，高度自动缩放(四舍五入取整数，px单位)

            if ($get_w < $this->res_w) {

                $c_h = $this->res_h / ($this->res_w / $get_w);
            } else {

                $c_h = $this->res_h * ($get_w / $this->res_w);
            }

            //画布宽度固定
            $c_w = $get_w;

            $r_w = $this->res_w;
            $r_h = $this->res_h;
        } elseif ($get_w == 0 && $get_h > 0) {
            //高度固定，宽度自动缩放

            if ($get_h < $this->res_h) {

                $c_w = $this->res_w / ($this->res_h / $get_h);
            } else {

                $c_w = $this->res_w * ($get_h / $this->res_h);
            }

            $c_h = $get_h;
            $r_w = $this->res_w;
            $r_h = $this->res_h;
        } elseif ($get_w > 0 && $get_h > 0) {
            //宽度，高度均设定，等比缩放至最合适进行裁切

            $c_w = $get_w;
            $c_h = $get_h;

            $r_w = $this->res_w;
            $r_h = $this->res_h;

            // var_dump('裁切模式');
            // 裁切模式（比例不合适显示不完整）
            if ($r_w / $c_w > $r_h / $c_h) {

                $r_w = $r_h / $c_h * $c_w;
            } else {

                $r_h = $r_w / $c_w * $c_h;
            }
        }

        return ['c_w' => $c_w, 'c_h' => $c_h, 'r_w' => $r_w, 'r_h' => $r_h];
    }

    //析构方法
    public function __destruct()
    {
    }
}
