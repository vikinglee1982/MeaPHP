<?php


namespace  MeaPHP\Core\Tools;

// header("Access-Control-Allow-Origin:*");
/*
@文件用途{

 ****图片验证码生成
}

@实现思路{

1产生随机验证码字符串
2创建空画布，并分配颜色
3绘制带填充的矩形
4绘制像素点
5写入一行ttf字符串
6输出图像，并销毁http://www.jinmayi.club/publiccore/method/captcha.php
7生成一个随机的字符串，包含英文大小写和数字
}

 */

class Captcha
{

        private static $obj = null;


        //阻止外部克隆书库工具类
        private function __clone()
        {
        }

        //私有化构造方法初始化，禁止外部使用
        private function __construct()
        {
        }
        //内部产生静态对象
        public static function start()
        {
                // echo "<hr>";
                // echo "建立了";
                // var_dump($dbkey);
                if (!self::$obj instanceof self) {
                        //如果不存在，创建保存
                        self::$obj = new self();
                }
                return self::$obj;
        }
        public function getImage()
        {

                $arr1 = array_merge(range('a', 'z'), range(0, 9), range('A', 'Z'));
                shuffle($arr1);
                // $arr2 = array_rand($arr1,4);
                // print_r($arr1);
                // print_r($arr2);
                //获得4个随机下标值
                $idx1 = mt_rand(0, 61);
                $idx2 = mt_rand(0, 61);
                $idx3 = mt_rand(0, 61);
                $idx4 = mt_rand(0, 61);
                // echo "<hr>";
                // echo $idx1;
                // echo "<hr>";
                // echo $idx2;
                // echo "<hr>";
                // echo $idx3;
                // echo "<hr>";
                // echo $idx4;
                // echo "<hr>";
                //最忌的字符串
                $str = $arr1[$idx1] . $arr1[$idx2] . $arr1[$idx3] . $arr1[$idx4];
                //将验证码字符串保存到session中，验证使用,不区分大小写
                // session_start();
                // //$_SESSION['captcha'] = strtolower($str);
                // echo $str;
                // //创建真彩色画布
                $width  = 100;
                $height = 22;
                $img    = imagecreatetruecolor($width, $height);
                // return $img;
                // //绘制带填充的矩形图片
                $color1 = imagecolorallocate($img, mt_rand(225, 255), mt_rand(225, 255), mt_rand(225, 255));
                imagefilledrectangle($img, 0, 0, $width, $height, $color1);

                // //绘制像素点
                for ($i = 1; $i <= 100; $i++) {
                        $color2 = imagecolorallocate($img, mt_rand(0, 20), mt_rand(180, 210), mt_rand(220, 255));
                        imagesetpixel($img, mt_rand(0, $width), mt_rand(0, $height), $color2);
                };
                // //绘制线段
                for ($i = 1; $i <= 10; $i++) {
                        $color3 = imagecolorallocate($img, mt_rand(0, 20), mt_rand(180, 210), mt_rand(220, 255));
                        imageline($img, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $color3);
                };
                // //绘制一行ttf字符串/jinmayiclub/WebSite/img
                if (file_exists(dirname($_SERVER['DOCUMENT_ROOT'])   . "/MeaPHP/Core/Font/msyh.ttf")) {
                        $fontfile = dirname($_SERVER['DOCUMENT_ROOT'])   . "/MeaPHP/Core/Font/msyh.ttf";
                } elseif (file_exists($_SERVER['DOCUMENT_ROOT']   . "/MeaPHP/Core/Font/msyh.ttf")) {
                        $fontfile = $_SERVER['DOCUMENT_ROOT']   . "/MeaPHP/Core/Font/msyh.ttf";
                }

                $color4   = imagecolorallocate($img, mt_rand(200, 255), mt_rand(0, 80), mt_rand(0, 80));

                imagettftext($img, 14, 0, 20, 18, $color4, $fontfile, $str);
                // //清除去除BMO头吗，某些情况可能因为bmo的原因生成失败

                return $img;
                // ob_clean();
                // header("content-type:image/png");
                // imagepng($img);
                // imagedestroy($img);

        }
}
