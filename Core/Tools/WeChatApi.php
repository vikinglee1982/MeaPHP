<?php
/*
 * @Author: vikinglee1982 87834084@qq.com
 * @Date: 2025-07-20 17:10:53
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2025-07-20 17:41:30
 * @FilePath: \工作台\Servers\huayun_server\MeaPHP\Core\Tools\WeChatApi.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE   
 */

namespace MeaPHP\Core\Tools;

use MeaPHP\Core\Reply\Reply;

class WeChatApi
{
    private static $obj = null;


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
    private function __clone() {}

    //私有化构造方法初始化，禁止外部使用
    private function __construct() {}

    public function getPhoneNumber($wxAppId, $wxAppSecret, $code): array
    {
        $token_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$wxAppId}&secret={$wxAppSecret}";
        $token_response = json_decode(file_get_contents($token_url), true);
        $access_token = $token_response['access_token'];
        // 第二步：调用微信接口获取手机号
        $phone_url = "https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token={$access_token}";
        $data = json_encode(['code' => $code]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $phone_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response, true);
        if ($response['code'] == 0) {
            return Reply::To(
                'ok',
                '连接了',
                $response['phone_info']
            );
        } else {
            return Reply::To('error', '连接失败', [
                'data' => $response['data']
            ]);
        }
        // return Reply::To('ok', '连接了', [
        //     'wxAppId' => $wxAppId,
        //     'wxAppSecret' => $wxAppSecret,
        //     'token_response' => $token_response,
        //     'access_token' => $access_token,
        //     'response' => $response
        // ]);
    }
}
