<?php


/**
 * 读取文件时生成缩略图///还未整理
 *
 * @package MeaPHP\Core\Tools
 * @author vikinglee1982 <87834084@qq.com>
 * @version 1.0
 * @copyright 2024 vikinglee1982
 * @license MIT
 * @link https://github.com/vikinglee1982/MeaPHP
 * @since 2024-04-05
 * 
 *需要生成图片的模式
 *$mode:
 *1：(入参中只有宽数值)以宽为准，高度自动缩放
 *2：(入参中只有高数值)以高为准，宽度自动缩放
 *3:(入参中宽、高都有数值)固定宽高，将图片等比放入
 *$fillColor:填充颜色
 *$res_t:文件类型
 *1 = GIF，2 = JPG，3 = PNG，4 = SWF，5 = PSD，6 = BMP，7 = TIFF(intel byte order)，8 = TIFF(motorola *byte order)，9 = JPC，10 = JP2，11 = JPX，12 = JB2，13 = SWC，14 = IFF，15 = WBMP，16 = XBM
 * 
 * 
 *
 */

namespace MeaPHP\Core\Tools;


use MeaPHP\Core\Reply\Reply;

class Fotophire
{
    private $img404;

    private static $obj = null;
    private $Res;

    //内部产生静态对象
    public static function active()
    {
        // var_dump( $dbkey );
        if (!self::$obj instanceof self) {
            //如果不存在，创建保存
            self::$obj = new self();
        }
        return self::$obj;
    }

    //阻止外部克隆书库工具类
    private function __clone()
    {
    }

    //私有化构造方法初始化，禁止外部使用
    private function __construct()
    {
        $this->img404 = realpath(dirname($_SERVER['DOCUMENT_ROOT']) . "/MeaPHP/Eikon") . "/image404.png";
        // $this->online  = $dbkey['online'];
        // $this->safeUrl = $dbkey['url'];

    }
    public function drawThumb(
        string $originalImgPath,
        int $destW = 0,
        int $destH = 0,
        string $destPath = null,
        string $type = 'extend'
    ) {
        //检查$imgPath是否存在；
        try {
            $filePathRes = $this->parsePath($originalImgPath);
            if ($filePathRes['sc'] == 'ok') {
                $filePathInfo = $filePathRes['data'];
            } else {
                return Reply::To('error', '图片地址解析失败');
            }
        } catch (\Exception $e) {
            return Reply::To('error', '错误信息', [
                'error' => $e->getMessage(),
            ]);
        }
        //检查宽度高度，至少有一个不是0

        if ($destW == '0' && $destH == '0') {
            return Reply::To('error', '生成图片尺寸：宽度和高度至少指定一个(单位:px)');
        }

        // $mode = null;
        // if ($destW != 0 && $destH == 0) {
        //     //如果宽度为0，则高度为0，则生成一个正方形图片
        //     $mode = 'w';
        // } elseif ($destW == 0 && $destH != 0) {
        //     //如果高度为0，则宽度为0，则生成一个正方形图片
        //     $mode = 'h';
        // } elseif ($destW != 0 && $destH != 0) {
        //     $mode = 'center';
        // }

        try {
            $destFileRes = $this->buildDestFullPath($filePathInfo, $destW, $destH, $destPath, $type);

            $saveLocalPath = $destFileRes['data']['destPath'];
            if ($destFileRes['sc'] == 'ok') {
                //获取图片的url
                $UrlRes = $this->localFileToUrl($saveLocalPath);

                if ($UrlRes['sc'] == 'ok') {
                    return Reply::To('ok', '预制图片生成链接成功', [
                        'url' => $UrlRes['data']['url'],
                        'filePathInfo' => $filePathInfo,
                    ]);
                } else {
                    return Reply::To('error', '预制图片生成链接失败');
                }
            } else {

                $makeRes = $this->makeImage($filePathInfo, $destW, $destH,  $saveLocalPath);

                if ($makeRes['sc'] == 'ok') {

                    $UrlRes = $this->localFileToUrl($makeRes['data']['path']);
                    if ($UrlRes['sc'] == 'ok') {
                        return Reply::To('ok', '图片保存成功', [
                            'url' => $UrlRes['data']['url'],
                        ]);
                    } else {
                        return Reply::To('error', $UrlRes['msg']);
                    }
                } else {
                    return Reply::To('error', '图片保存失败', ['err' => $makeRes['msg']]);
                }
            }
        } catch (\Exception $e) {
            return Reply::To('error', '错误信息', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @description:解析路径
     * @param {*} $path
     * @return {*}
     */
    public function parsePath(string $path): array
    {
        //判断入参是绝对路径还是相对路径；输出绝对路径

        if (strpos($path, $_SERVER['DOCUMENT_ROOT']) === false) {
            //入参是没有根目录的相对路径（不包含根目录）
            $localPath =  $_SERVER['DOCUMENT_ROOT'] . $path;
        } else {
            $localPath =  $path;
            // return '有根目录';
        }

        if (file_exists($localPath)) {
            return Reply::To('ok', '返回参数', [
                'localPath' => $localPath,
                'fileFullName' => basename($localPath),
                'fileName' => pathinfo($localPath, PATHINFO_FILENAME),
                'fileType' => pathinfo($localPath, PATHINFO_EXTENSION),
                'fileSize' => filesize($localPath),
                'fileMd5' => md5_file($localPath),
                'fileMime' => mime_content_type($localPath),
            ]);
        } else {
            return Reply::To('err', '文件不存在');
        }
    }

    /**
     * @description:生成目标文件路径
     * @param {*} $filePathInfo
     * @param {*} $destW
     * @param {*} $destH
     * @param {*} $destPath
     * @param {*} $type
     * @return {*}
     */
    public function buildDestFullPath(
        array $filePathInfo,
        int $destW,
        int $destH,
        string $destPath = null,
        string $type
    ): array {

        if (!$destPath) {
            $extension = $type == 'extend' ? $filePathInfo['fileType'] : $type;
            $destPath =  dirname($filePathInfo['localPath']) . '/' . $filePathInfo['fileName'] . '-' .  $destW . '_' . $destH  . '.' . $extension;
        }

        if (file_exists($destPath)) {
            return Reply::To('ok', '文件已经存在', [
                'destPath' => $destPath,
            ]);
        } else {
            return Reply::To('err', '文件不存在',  [
                'destPath' => $destPath,
            ]);
        }
    }
    /**
     * @description:将本地文件转换为网络Url
     * @param {*} $localPath
     * @return {*}
     */
    public function localFileToUrl(string $localPath): array

    {

        $localPathRes = $this->parsePath($localPath);
        if ($localPathRes['sc'] == 'ok') {
            $localPath = $localPathRes['data']['localPath'];
        } else {
            return Reply::To('err', $localPathRes['msg']);
        }
        // 判断当前请求是否为HTTPS
        $isHttps = (
            (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        );

        // 使用当前域名拼接本地地址
        $protocol = $isHttps ? 'https://' : 'http://';
        $url = $protocol . $_SERVER['SERVER_NAME'] . str_replace($_SERVER['DOCUMENT_ROOT'], '', $localPath);

        if (file_exists($localPath)) {

            // 返回文件存在时的数组（假设Reply::To是一个自定义类/结构，保持原样）
            return Reply::To('ok', '文件已经存在', [
                'url' => $url,
            ]);
        } else {
            // 返回文件不存在时的数组（假设Reply::To是一个自定义类/结构，保持原样）
            return Reply::To('err', '文件不存在', ['$localPathRes' => $localPathRes]);
        }
    }
    /**
     * @description:生成图片资源
     * @param {*} $filePathInfo
     * @param {*} $destW
     * @param {*} $destH
     * @param {*} $saveLocalPath
     * @return {*}
     */

    private function makeImage(array $fileInfo, int $destW, int $destH, string $savePath): array
    {
        //生成图像资源
        $res = $this->resourceHandler($fileInfo['localPath']);

        if ($res['sc'] != 'ok') {
            return Reply::To('err', $res['msg']);
        }

        $originalWidth = $res['data']['res_w'];
        $originalHeight = $res['data']['res_h'];
        $imgTypeCode = $res['data']['res_t'];

        $sizeRes = $this->createCanvasSize($originalWidth, $originalHeight, $destW, $destH);
        $canvasWidth = $sizeRes['data']['width'];
        $canvasHeight = $sizeRes['data']['height'];
        $canvas = imagecreatetruecolor($canvasWidth, $canvasHeight);

        //如果是png
        if ($imgTypeCode == 3) {
            // 给画布上底色
            $back = imagecolorallocate($canvas, 0, 0, 0);
            //填充颜色，
            imagefill($canvas, 0, 0, $back);
            //将底色设置透明（设置颜色代号，在设置透明）
            imagecolortransparent($canvas, $back);
        }

        //         //复制拷贝图像

        imagecopyresampled($canvas, $this->Res, 0, 0, 0, 0, $canvasWidth, $canvasHeight, $originalWidth, $originalHeight);

        $saveRes = $this->save($canvas, $savePath, $imgTypeCode);

        if ($saveRes['sc'] == 'ok') {
            return Reply::To('ok', '图片生成成功', ['path' => $saveRes['data']['path']]);
        } else {
            return Reply::To('err', $saveRes['msg']);
        }
    }


    /**
     * @description:入参资源判定并生成资源
     * @param {*} $filePathInfo
     * @param {*} $destW
     * @param {*} $destH
     * @param {*} $saveLocalPath
     * @return {*}
     */
    private function resourceHandler($imagePath)
    {
        try {
            if (!is_file($imagePath) || getimagesize($imagePath) === false) {
                // throw new Exception("入参的资源不存在或者不是图片类型");
                return Reply::To('err', '入参的资源不存在或者不是图片类型');
            }

            $fileInfo = getimagesize($imagePath);
            $imageTypeMap = [
                1 => 'imagecreatefromgif',
                2 => 'imagecreatefromjpeg',
                3 => 'imagecreatefrompng',
            ];

            if (!array_key_exists($fileInfo[2], $imageTypeMap)) {
                // throw new Exception("图片格式支持gif\jpg\png");
                return Reply::To('err', '图片格式支持gif\jpg\png');
            }

            $imageCreateFunction = $imageTypeMap[$fileInfo[2]];
            //资源数据不能通过函数直接返回，否则不显示任何返回信息
            $this->Res = $imageCreateFunction($imagePath);

            // 对于PNG图片，设置透明度
            if ($fileInfo[2] == 3) {
                imagesavealpha($this->Res, true);
            }

            // 返回图像信息和资源
            return Reply::To('ok', '抓取资源成功', [

                'res_w' => $fileInfo[0],
                'res_h' => $fileInfo[1],
                'res_t' => $fileInfo[2],
            ]);
        } catch (\Exception $e) {
            // 记录日志（这里假设存在一个日志记录函数logException）
            // 返回错误消息
            return Reply::To('err', '抓取资源失败', ['err' => $e->getMessage()]);
        }
    }
    /**
     * @description:生成画布尺寸
     * @param {*} $originalWidth
     * @param {*} $originalHeight
     * @param {*} $destW
     * @param {*} $destH
     * @return {*}
     */
    private function createCanvasSize(int $res_w, int $res_h, int $dest_w, int $dest_h): array
    {
        // 计算目标宽高比和原始宽高比
        $target_ratio = $dest_w / ($dest_h ?: 1); // 修正变量名，使用 $dest_w 和 $dest_h
        $original_ratio = $res_w / $res_h;

        // 根据目标宽高比与原始宽高比的关系确定缩放策略
        if ($target_ratio > $original_ratio) {
            // 目标更宽，按宽度缩放，高度自适应
            $width = $dest_w;
            $height = round($dest_w / $original_ratio);
        } elseif ($target_ratio < $original_ratio) {
            // 目标更高，按高度缩放，宽度自适应
            $height = $dest_h;
            $width = round($dest_h * $original_ratio);
        } else {
            // 宽高比相同，直接缩放到目标尺寸
            $width = $dest_w;
            $height = $dest_h;
        }

        // 原始尺寸保持不变
        // $r_w = $res_w;
        // $r_h = $res_h;

        return Reply::To('ok', '返回参数', compact('width', 'height'));
    }


    /**
     * @description:文件保存
     * @param {*} $file
     * @param {*} $savePath
     * @param {*} $imgTypeCode
     * @return {*}
     */
    private function save($file, string $savePath, int $imgTypeCode): array
    {
        try {
            switch ($imgTypeCode) {
                case 1:
                    // GIF 文件保存
                    imagegif($file, $savePath);
                    return Reply::To('ok', '资源保存成功', ['path' => $savePath]);
                    break;
                case 2:
                    // JPEG 文件保存
                    imagejpeg($file, $savePath);
                    return Reply::To('ok', '资源保存成功', ['path' => $savePath]);
                    break;
                case 3:
                    // PNG 文件保存
                    imagepng($file, $savePath);
                    return Reply::To('ok', '资源保存成功', ['path' => $savePath]);
                    break;
                default:
                    return Reply::To('err', '未知的图片类型代码，资源保存失败');
            }
        } catch (\Exception $e) {

            return Reply::To('err', '资源保存失败', ['err' => $e->getMessage()]);
        } finally {
            //销毁资源
            imagedestroy($file);
        }
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





    //析构方法
    public function __destruct()
    {
    }
}
