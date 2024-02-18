<?php
/*
 * @Author: vikinglee1982 87834084@qq.com
 * @Date: 2024-02-04 16:50:37
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2024-02-17 10:01:14
 * @FilePath: \工作台\Servers\lzkj_server\MeaPHP\Core\Tools\CreateImage.php
 * @Description: 生成图片，使用GD库，支持验证码、微信朋友圈图片、缩略图、水印图
 */


namespace MeaPHP\Core\Tools;



class ImageFactory
{
    private $img404;


    private static $obj = null;
    //阻止外部克隆书库工具类
    private function __clone()
    {
    }

    //私有化构造方法初始化，禁止外部使用
    private function __construct()
    {
        $this->img404 = realpath(dirname($_SERVER['DOCUMENT_ROOT']) . "/MeaPHP/Eikon") . "/image404.png";
    }
    //内部产生静态对象
    public static function active()
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

    /**
     * @description: 生成微信小程序朋友圈海报
     * @param {string} $img
     * @param {string} $appletQrCode
     * @param {array} $pathParma
     * @return {*}
     */
    function WeChatPoster(
        string $imgUrl,
        string $appletQrCodeUrl,
        string $title,

        // array $pathParams = [],
        //是否显示旅游网或者旅行社的logo或徽章
        //是否显示用户的用户名头像等相关信息
        //如果这些都不显示，需要设置一个默认的宣传语
        //需要设置一个图片加载失败的默认图片
        array $userInfo = [
            'avater' => '',
            'name' => 'viking'
        ],
        array $logo = [
            'path' => '',
            'title' => '',
        ],

        string $hint = '长按识别小程序码查看详情',
        int $titleSize = 36,
        int $textSize = 24,
        int $padding = 16,
        int $qrcodeZoneHeight = 200
    ): array {
        //  打开需要生成海报的背景图片文件
        $bgImage = $this->createImageResourceFromAny($imgUrl);
        if (!$bgImage) {

            $bgImage = $this->createImageResourceFromAny($this->img404);
            if (!$bgImage) {
                return [
                    'sc' => 'err',
                    'msg' => '背景图片加载失败，缺省图片加载失败',
                    'data' => $this->img404,
                ];
            }
        }
        // 海报图片的尺寸
        $sw = imagesx($bgImage);
        $sh = imagesy($bgImage);
        // 设置图片新的宽度为1080
        $w = 1080;

        // 计算保持比例下的新高度
        $h = round($w * ($sh / $sw));


        $titleZoneHeight = $textSize * 4;

        $backgroundHeight = $h + $qrcodeZoneHeight + $textSize * 3 + $titleZoneHeight;

        // 创建一个新的图像资源
        $background = imagecreatetruecolor($w, $backgroundHeight);

        // // 设置背景颜色
        // 236, 245, 255//255, 223, 4
        $backgroundColor1 = imagecolorallocate($background, 255, 255, 255);
        imagefill($background, 0, 0, $backgroundColor1);

        // 复制背景图片到新创建的图片资源上
        imagecopyresampled($background, $bgImage, 0, $titleZoneHeight, 0, 0, $w, $h, imagesx($bgImage), imagesy($bgImage));
        imagedestroy($bgImage);

        //创建二维码区域的图片

        $qrZoneBackground = imagecreatetruecolor($w, $qrcodeZoneHeight);
        $backgroundColor2 = imagecolorallocate($qrZoneBackground, 244, 244, 245);
        imagefill($qrZoneBackground, 0, 0, $backgroundColor2);

        imagecopyresampled($background, $qrZoneBackground, 0, $h + $titleZoneHeight, 0, 0, $w, $qrcodeZoneHeight, imagesx($qrZoneBackground), imagesy($qrZoneBackground));
        imagedestroy($qrZoneBackground);

        //创建底部提示文字区域的图片

        $hintZoneBackground = imagecreatetruecolor($w, $textSize * 3);
        $backgroundColor3 = imagecolorallocate($hintZoneBackground, 255, 223, 4);
        imagefill($hintZoneBackground, 0, 0, $backgroundColor3);

        $hintZoneY = $h + $titleZoneHeight + $qrcodeZoneHeight;
        $hintZoneHeight =  $textSize * 3;
        imagecopyresampled($background, $hintZoneBackground, 0, $hintZoneY, 0, 0, $w, $hintZoneHeight, imagesx($hintZoneBackground), imagesy($hintZoneBackground));

        // 设置字体文件路径

        $fontFile = $this->getFontFilePath();
        if (!$fontFile) {
            return [
                'sc' => 'err',
                'msg' => '字体文件加载失败',
            ];
        }

        /**
         * @description: 添加底部提示文字
         * @return {*}
         */
        //计算文字宽度
        $textWidth =   $this->getTextWidth($hint, $fontFile, $textSize);

        // 计算文字居中位置（水平方向）
        $textX = ($w - $textWidth) / 2;

        // 文字垂直居中在图片底部
        $textY = $backgroundHeight  - $textSize;
        //  textSize 是文字高度，可以适当增加一些间距以美观展示
        //如果提示文字超过长度打点显示
        $hint = $this->getFittedText($hint, $fontFile, $textSize, $w - $padding * 4);
        // 使用指定的颜色、字体、字号和坐标写入文字到背景图片上
        imagettftext($background, $textSize, 0, $textX, $textY, imagecolorallocate($background, 61, 60, 153), $fontFile, $hint);

        /**
         * @description: 添加标题
         * @return {*}
         */


        $titleMaxWidth = 1080 - $padding * 6; // 标题最大宽度

        //如果标题长度超出图片宽度，打点显示
        $title = $this->getFittedText($title, $fontFile, $titleSize, $titleMaxWidth);

        // 计算标题文字居中位置（水平方向）
        $titleTextX = $padding;
        // 文字垂直居中在图片顶部
        $titleTextY = $titleZoneHeight / 3 + $titleSize;
        // 设置标题文字颜色
        $titleTextColor = imagecolorallocate($background, 0, 0, 0);
        // 使用指定的颜色、字体、字号和坐标写入文字到背景图片上
        imagettftext($background, $titleSize, 0, $titleTextX, $titleTextY, $titleTextColor, $fontFile, $title);

        //添加二维码
        $qrCodeImage = $this->createImageResourceFromAny($appletQrCodeUrl);
        if (!$qrCodeImage) {
            return [
                'sc' => 'err',
                'msg' => '二维码加载失败',
            ];
        }

        $padding = 16;
        // 打开并添加二维码图片
        // 二维码在背景图片中的y轴开始位置
        $qrCodeStartY = $h + $padding + $titleZoneHeight;

        //获取二维码原尺寸
        $qrCodeWidth = imagesx($qrCodeImage);
        $qrCodeHeight = imagesy($qrCodeImage);

        $qh = $qrcodeZoneHeight - ($padding * 2);
        // 计算保持比例下的新宽度
        $qw = round($qh * ($qrCodeWidth / $qrCodeHeight));

        //计算二维码在背景图片中的x轴开始位置
        $qrCodeStartX = $w - $qw - $padding;

        imagecopyresampled($background, $qrCodeImage, $qrCodeStartX, $qrCodeStartY, 0, 0, $qw, $qh, imagesx($qrCodeImage), imagesy($qrCodeImage));
        imagedestroy($qrCodeImage);

        //二维码区域内添加文字
        // $qrCodeTextX = $qrCodeStartX + ($qw / 2) - (strlen($qrCodeText) * $qrCodeTextSize / 2);








        // 生成最终的海报图片文件
        ob_start();
        imagejpeg($background); // 第二个参数NULL表示直接输出到浏览器，这里我们可以将其改为文件路径
        $posterData = ob_get_clean(); // 清空并获取缓冲区内容，但在我们直接写入文件的情况下，这部分不需要了

        // 直接保存为jpg文件
        // $file_path = $_SERVER['DOCUMENT_ROOT'] . 'Resource/poseter/'; // 指定要保存的文件路径
        // file_put_contents($file_path, $posterData); // 将图片数据写入到指定路径的文件中
        // $res = $this->severImage($posterData, $file_path);

        // 释放图片资源
        imagedestroy($background);

        return [
            'sc' => 'ok',
            'imgUrl' => $imgUrl,
            '$w' => $w,
            '$h' => $h,
            'textX' => $textX,
            'textY' => $textY,
            '$title' => $title,
            '$textWidth' => $textWidth,
            'qrCodeStartY' => $qrCodeStartY,
            // 'posterData' => $posterData,
            '$fontFile ' =>  $fontFile,
            'appletQrCodeUrl' => $appletQrCodeUrl,

            'data' => 'data:image/jpeg;base64,' . base64_encode($posterData),
        ];
    }

    // 创建图片资源
    private function createImageResourceFromAny($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'jpg':
                return imagecreatefromjpeg($filename);
            case 'jpeg':
                return imagecreatefromjpeg($filename);
            case 'png':
                return imagecreatefrompng($filename);
            case 'gif':
                return imagecreatefromgif($filename);
            case 'bmp':
                return imagecreatefromwbmp($filename);
                // 如果您的GD库支持WebP，则添加：
            case 'webp':
                return imagecreatefromwebp($filename);
            default:
                trigger_error("不支持的图片格式: {$extension}", E_USER_WARNING);
                return false;
        }
    }

    /**
     * @description: 获取字体文件路径
     * @return {*}
     */
    private function getFontFilePath(): string
    {
        // 检查字体文件是否存在
        // 如果没有指定字体路径，尝试查找内置字体
        if (file_exists(dirname($_SERVER['DOCUMENT_ROOT'])   . "/MeaPHP/Font/msyh.ttf")) {
            $fontfile = dirname($_SERVER['DOCUMENT_ROOT'])   . "/MeaPHP/Font/msyh.ttf";
        } elseif (file_exists($_SERVER['DOCUMENT_ROOT']   . "/MeaPHP/Font/msyh.ttf")) {
            $fontfile = $_SERVER['DOCUMENT_ROOT']   . "/MeaPHP/Font/msyh.ttf";
        }
        if (!empty($fontfile) && file_exists($fontfile)) {
            return  $fontfile;
        } else {
            return false;
        }
    }

    /**
     * @description: 获取文字的宽度
     * @param {string} $text
     * @param {string} $fontFile
     * @param {int} $fontSize
     * @return {*}
     */
    private function getTextWidth(string $text, string $fontFile, int $fontSize): int
    {
        $bbox = imagettfbbox($fontSize, 0, $fontFile, $text);
        return (int) ($bbox[2] - $bbox[6]);
    }

    private  function getFittedText($originalText, $fontFile, $fontSize, $maxWidth)
    {
        $text = $originalText;
        $textBox = $this->getTextWidth($text, $fontFile, $fontSize); // 假设measureTextWidth返回文本宽度

        while ($textBox > $maxWidth && mb_strlen($text) > 0) {
            // 截取部分文本并尝试
            $text = mb_substr($text, 0, -1); // 移除最后一个字符
            $textBox = $this->getTextWidth($text, $fontFile, $fontSize);
        }

        return $text . (strlen($originalText) !== strlen($text) ? '...' : ''); // 若进行了截断则添加省略号
    }



    private function severImage(array $img, string $path): array
    {

        return [];
    }
}
