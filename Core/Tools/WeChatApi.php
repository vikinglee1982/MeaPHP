<?php
/*
 * @Author: vikinglee1982 87834084@qq.com
 * @Date: 2025-07-20 17:10:53
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2025-07-24 16:27:07
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
    private function getAccessToken($wxAppId, $wxAppSecret): array
    {
        $token_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$wxAppId}&secret={$wxAppSecret}";
        $token_response = json_decode(file_get_contents($token_url), true);
        $access_token = $token_response['access_token'];
        if ($access_token) {
            return Reply::To(
                'ok',
                '获取access_token成功',
                [
                    'access_token' => $access_token
                ]
            );
        } else {
            return Reply::To('error', '获取access_token失败', [
                'data' => $token_response['data']
            ]);
        }
    }
    public function getPhoneNumber($AppID, $AppSecret, $code): array
    {
        $accessTokenRes = $this->getAccessToken($AppID, $AppSecret);
        if ($accessTokenRes['sc'] != 'ok') {
            return Reply::To('error', '获取access_token失败', [
                'data' => $accessTokenRes['data']
            ]);
        }
        $accessToken = $accessTokenRes['data']['access_token'];

        // 第二步：调用微信接口获取手机号
        $phone_url = "https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token={$accessToken}";
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
    /**
     * 获取小程序无限二维码（带参数）
     *
     * @param string $accessToken 微信 access_token
     * @param array $params 二维码参数，例如 ['scene' => 'user123', 'page' => 'pages/index/index']
     * @return array
     */
    public function getWeQRCode(string $AppID, string $AppSecret, string $pid): array
    {
        $accessTokenRes = $this->getAccessToken($AppID, $AppSecret);
        if ($accessTokenRes['sc'] != 'ok') {
            return Reply::To('error', '获取access_token失败', [
                'data' => $accessTokenRes['data']
            ]);
        }
        $accessToken = $accessTokenRes['data']['access_token'];
        // 使用 base64_encode(json_encode($params)) 构造 scene
        // $paramsStr = "";
        // foreach ($params as $key => $value) {
        //     $paramsStr .= $key . '=' . $value . '&';
        // }
        // $paramsStr = rtrim($paramsStr, '&');
        // $hash = hash('sha1', $paramsStr, true); // true 表示返回二进制数据

        // $scene = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($hash));


        // 检查长度是否合法
        if (strlen($pid) > 32) {
            return Reply::To('err', 'scene 参数 base64 编码后长度超过 32', [
                'scene' => $pid,
                'len' => strlen($pid)
            ]);
        }

        // 默认参数
        $defaultParams = [
            'scene' => $pid,
            'page' => 'pages/details_trip/details_trip',
            // 'page' => '/pages/details_trip/details_trip',
            'width' => 200,
            'auto_color' => false,
            'line_color' => ['r' => '0', 'g' => '0', 'b' => '0'],
            'is_hyaline' => true
        ];

        //将数组params与json
        // $params = json_encode($params);

        // $postData = array_merge($defaultParams, $params);

        // // 确保 scene 是字符串或数字（微信限制）
        // if (!is_string($postData['scene']) && !is_numeric($postData['scene'])) {
        //     return Reply::To('err', 'scene 参数必须为字符串或数字', [$postData]);
        // }

        // if (strlen((string)$postData['scene']) > 32) {
        //     return Reply::To('err', 'scene 参数长度不能超过 32 个字符');
        // }


        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token={$accessToken}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($defaultParams));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 生产环境请开启验证
        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            return Reply::To('请求失败：' . curl_error($ch));
        }

        curl_close($ch);

        // 判断是否是图片数据
        $contentType = @finfo_buffer(finfo_open(), $result, FILEINFO_MIME_TYPE);
        if (strpos($contentType, 'image') !== false) {
            return Reply::To('ok', '二维码生成成功', [
                'qrcode' => 'data:' . $contentType . ';base64,' . base64_encode($result)
            ]);
        } else {
            // 错误信息
            $json = json_decode($result, true);
            return Reply::To('err', '微信返回错误：' . ($json['errmsg'] ?? '未知错误') . " (errcode: {$json['errcode']})", [$defaultParams]);
        }
    }
}
