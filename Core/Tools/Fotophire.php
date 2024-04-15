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

use GdImage;
use MeaPHP\Core\Reply\Reply;

class Fotophire
{

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
            return Reply::To('err', '文件不存在');
        }
    }

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


    //入参资源判定并生成资源
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

    // //文件保存
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




    //析构方法
    public function __destruct()
    {
    }
}
