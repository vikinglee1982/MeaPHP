<?php
/*
 * @Author: vikinglee1982 87834084@qq.com
 * @Date: 2024-03-12 10:14:58
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2026-05-16 17:41:31
 * @FilePath: \工作台\Servers\huayun_server\MeaPHP\Bootstrap\SetHeader.php
 * @Description: 解决跨域问题设置报头
 */

namespace MeaPHP\Bootstrap;

class Header
{
    public static function set()
    {

        // $origin = $_SERVER['HTTP_ORIGIN'];

        // header("Access-Control-Allow-Origin: {$origin}");
        // header("Access-Control-Allow-Origin: https://www.huayunlvyou.com");
        // header("Access-Control-Allow-Origin: https://huayunlvyou.com");

        // header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        // header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, euid,Euid,token,ecid,Ecid,esid,Esid");
        // // 添加'euid'到允许的请求头列表
        // header("Access-Control-Allow-Credentials: true"); // 必须明确设置为true
        // 获取 Origin，若不存在则设为空字符串
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // 定义多个允许的主域名（不带协议和子域名）
        $allowedDomains = [
            'huayunlvyou.com',
            'tangchennongye.com',
            // 'mytravel.cn',
            // 可继续添加...
        ];

        $isAllowedOrigin = false;

        if ($origin !== '') {
            foreach ($allowedDomains as $domain) {
                // 构建正则：^https://([a-zA-Z0-9.-]*\.)?domain$
                $pattern = '#^https://([a-zA-Z0-9.-]*\.)?' . preg_quote($domain, '#') . '$#';
                if (preg_match($pattern, $origin)) {
                    $isAllowedOrigin = true;
                    break; // 匹配到一个即可
                }
            }

            // ← 新增: 允许本地开发环境 (http://localhost, http://127.0.0.1, http://192.168.x.x)
            // if (!$isAllowedOrigin) {
            //     $localPattern = '#^https?://(localhost|127\.0\.0\.1|192\.168\.\d+\.\d+)(:\d+)?$#';
            //     if (preg_match($localPattern, $origin)) {
            //         $isAllowedOrigin = true;
            //     }
            // }
        }

        // 如果是合法来源，设置 CORS 头
        if ($isAllowedOrigin) {
            header("Access-Control-Allow-Origin: {$origin}");
            header("Access-Control-Allow-Credentials: true");
        }

        // 其他 CORS 头（必须始终设置，否则预检失败）
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, euid, Euid, token, ecid, Ecid, esid, Esid");

        // 预检请求直接返回
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    /**
     * 验证请求来源是否（未使用，已经通过了用户配置的黑白名单审核）
     */
    private static function isValidOrigin(string $origin): bool
    {
        // 根据您的实际需求验证$origin是否合法，这里仅作为示例：
        $hostParts = explode('.', $_SERVER['HTTP_HOST']);
        $topLevelDomain = implode('.', array_slice($hostParts, -2));

        // 检查请求来源的顶级域名是否与当前服务器相同
        $originParts = explode('.', parse_url($origin, PHP_URL_HOST));
        $originTLD = implode('.', array_slice($originParts, -2));

        return $originTLD === $topLevelDomain;
    }
}
