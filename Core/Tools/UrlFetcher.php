<?php
/*
 * @Author: vikinglee1982 87834084@qq.com
 * @Date: 2026-03-24 16:31:28
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2026-03-25 17:09:00
 * @FilePath: \工作台\Servers\huayun_server\MeaPHP\Core\Tools\UrlFetcher.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace  MeaPHP\Core\Tools;

use MeaPHP\Core\Reply\Reply;

class UrlFetcher
{

    public static $Obj;
    //内部产生静态对象
    public static function active()
    {
        // var_dump($config);
        // echo "开始创建<hr>";
        if (!self::$Obj instanceof self) {
            // echo "不存在，创建了<hr>";
            //如果不存在，创建保存
            self::$Obj = new self();
        }
        return self::$Obj;
    }
    //阻止外部克隆书库工具类
    private function __clone() {}

    //构造方法初始化，属性赋值，准备连接
    private function __construct() {}
    public function getImage(string $url): array
    {
        // 1. 验证 URL 格式
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return Reply::To('error', '无效的 URL 地址');
        }

        // 2. 仅允许 HTTP/HTTPS
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array(strtolower($scheme ?? ''), ['http', 'https'], true)) {
            return Reply::To('error', '仅支持 HTTP/HTTPS 协议');
        }

        // 3. 初始化 cURL
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_USERAGENT      => 'ResourceFetcher/1.0',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_MAXREDIRS      => 5,
        ]);

        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $error = curl_error($ch);
        curl_close($ch);

        // 4. 检查请求是否成功
        if ($content === false || $httpCode !== 200) {
            return Reply::To('error', '获取图片失败: ' . ($error ?: "HTTP {$httpCode}"));
        }

        // 5. 检查内容是否为空
        if ($content === '') {
            return Reply::To('error', '获取到的内容为空');
        }

        // // 6. 初步 MIME 检查（兼容 PHP 7.4）
        $contentType = is_string($contentType) ? trim($contentType) : '';
        if ($contentType !== '' && strpos($contentType, 'image/') !== 0) {
            return Reply::To('error', '返回内容不是图片（MIME: ' . $contentType . '）');
        }

        // 7. 创建临时文件
        $tmpFile = tempnam(sys_get_temp_dir(), 'remote_img_');
        if ($tmpFile === false) {
            return Reply::To('error', '无法创建临时文件');
        }

        if (file_put_contents($tmpFile, $content) === false) {
            return Reply::To('error', '写入临时文件失败');
        }

        // 8. 获取真实文件信息
        $size = filesize($tmpFile);
        if ($size === false || $size === 0) {
            unlink($tmpFile);
            return Reply::To('error', '临时文件大小无效');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMimeType = finfo_file($finfo, $tmpFile);
        finfo_close($finfo);

        // // 9. 最终 MIME 验证（兼容 PHP 7.4）
        if (!is_string($realMimeType) || strpos($realMimeType, 'image/') !== 0) {
            unlink($tmpFile);
            return Reply::To('error', '文件实际类型不是图片（检测为: ' . ($realMimeType ?: 'unknown') . '）');
        }

        // 10. 提取或生成文件名
        $path = parse_url($url, PHP_URL_PATH);
        $originalName = basename($path);
        if (!$originalName || !preg_match('/\.(jpg|jpeg|png|gif|webp|bmp)$/i', $originalName)) {
            // 根据 MIME 推断扩展名
            $extMap = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                // 'image/gif'  => 'gif',
                // 'image/webp' => 'webp',
                // 'image/bmp'  => 'bmp',
            ];
            $ext = $extMap[$realMimeType] ?? 'jpg';
            $originalName = 'remote_image.' . $ext;
        }

        // 11. 构造类 $_FILES 数组
        $file = [
            'name'     => $originalName,
            'type'     => $realMimeType,
            'tmp_name' => $tmpFile,
            'error'    => 0,
            'size'     => $size,
            
        ];

        return Reply::To('ok', '获取成功', $file);
    }
}
