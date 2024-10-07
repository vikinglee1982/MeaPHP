<?php
/*
 * @Author: vikinglee1982 87834084@qq.com
 * @Date: 2024-03-12 10:14:58
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2024-10-05 11:14:17
 * @FilePath: \工作台\Servers\huayun_server\MeaPHP\Bootstrap\SetHeader.php
 * @Description: 解决跨域问题设置报头
 */

namespace MeaPHP\Bootstrap;

class Header
{
    public static function set()
    {

        $origin = $_SERVER['HTTP_ORIGIN'];

        header("Access-Control-Allow-Origin: {$origin}");

        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, euid,token");
        // 添加'euid'到允许的请求头列表
        header("Access-Control-Allow-Credentials: true"); // 必须明确设置为true

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
