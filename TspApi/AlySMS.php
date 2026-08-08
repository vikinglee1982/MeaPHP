<?php
/*
 * @Author: vikinglee1982 87834084@qq.com
 * @Date: 2024-08-26 14:09:25
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2026-05-29 16:11:44
 * @FilePath: \工作台\Servers\huayun_server\MeaPHP\TspApi\AlySMS.php
 * @Description: 阿里云短信发送工具类（纯 cURL 实现，无需 Composer）
 */

namespace MeaPHP\TspApi;

use Exception;

/**
 * 阿里云 SMS 短信发送工具类
 * 
 * 无外部依赖，纯 PHP cURL 调用阿里云 OpenAPI。
 * 
 * 使用示例:
 * $sms = new AlySMS('AccessKeyID', 'AccessKeySecret');
 * $result = $sms->send('13800138000', '签名名称', 'SMS_模板ID', ['code' => '123456']);
 */
class AlySMS
{
    private static $obj = null;

    /**
     * API 域名
     */
    private $endpoint = 'dysmsapi.aliyuncs.com';

    /**
     * API 版本
     */
    private $version = '2017-05-25';

    /**
     * 签名算法版本
     */
    private $signatureVersion = '1.0';

    /**
     * 签名方式
     */
    private $signatureMethod = 'HMAC-SHA1';

    /**
     * AccessKey ID
     */
    private $accessKeyId;

    /**
     * AccessKey Secret
     */
    private $accessKeySecret;

    // 阻止外部克隆
    private function __clone() {}

    // 私有化构造方法，禁止外部使用
    private function __construct() {}

    /**
     * 内部产生静态对象
     * 
     * 支持两种调用方式：
     * 1. AlySMS::active('AK', 'SK')          — 直接传 Key
     * 2. AlySMS::active($UserConfig)         — 从配置数组读取（框架自动注入）
     *    配置项：$UserConfig['aly_sms']['ak_id']、$UserConfig['aly_sms']['ak_secret']
     * 
     * @param string|array $param1     AccessKey ID 或 UserConfig 数组
     * @param string       $param2     AccessKey Secret（方式1时使用）
     * @return self
     */
    public static function active($param1 = '', $param2 = '')
    {
        if (!self::$obj instanceof self) {
            self::$obj = new self();
        }

        // 判断传入的是数组（框架注入 UserConfig）还是字符串（直接传 Key）
        if (is_array($param1)) {
            // 从 UserConfig 中读取
            $smsConfig = $param1['aly_sms'] ?? [];
            $akId      = $smsConfig['ak_id'] ?? '';
            $akSecret  = $smsConfig['ak_secret'] ?? '';
        } else {
            // 直接传参
            $akId     = $param1;
            $akSecret = $param2;
        }

        if (!empty($akId)) {
            self::$obj->accessKeyId = $akId;
        }
        if (!empty($akSecret)) {
            self::$obj->accessKeySecret = $akSecret;
        }
        return self::$obj;
    }

    /**
     * 发送短信
     * 
     * @param string $phone        手机号码（支持多个，逗号分隔）
     * @param string $signName     短信签名
     * @param string $templateCode 模板 ID
     * @param array  $params       模板参数
     * @return array ['code'=>'OK','message'=>'成功','bizId'=>'xxx']
     */
    public function send($phone, $signName, $templateCode, $params = [])
    {
        try {
            $apiParams = [
                'RegionId'         => 'cn-hangzhou',
                'PhoneNumbers'     => $phone,
                'SignName'         => $signName,
                'TemplateCode'     => $templateCode,
                'TemplateParam'    => json_encode($params, JSON_UNESCAPED_UNICODE),
                'Action'           => 'SendSms',
                'Version'          => $this->version,
                'AccessKeyId'      => $this->accessKeyId,
                'SignatureMethod'  => $this->signatureMethod,
                'SignatureVersion' => $this->signatureVersion,
                'SignatureNonce'   => $this->uuid(),
                'Timestamp'        => gmdate('Y-m-d\TH:i:s\Z'),
                'Format'           => 'JSON',
            ];

            $apiParams['Signature'] = $this->sign($apiParams);

            $response = $this->httpPost($apiParams);
            $result   = json_decode($response, true);

            return [
                'code'    => $result['Code'] ?? 'FAIL',
                'message' => $result['Message'] ?? '发送失败',
                'bizId'   => $result['BizId'] ?? '',
            ];
        } catch (Exception $e) {
            return [
                'code'    => 'ERROR',
                'message' => $e->getMessage(),
                'bizId'   => '',
            ];
        }
    }

    /**
     * 批量发送（相同内容）
     * 
     * @param array  $phones       手机号数组
     * @param string $signName     短信签名
     * @param string $templateCode 模板 ID
     * @param array  $params       模板参数
     * @return array
     */
    public function batchSend($phones, $signName, $templateCode, $params = [])
    {
        $results = [];
        foreach ($phones as $phone) {
            $results[$phone] = $this->send($phone, $signName, $templateCode, $params);
        }
        return $results;
    }

    // ==================== 内部方法 ====================

    /**
     * 生成签名
     */
    private function sign($params)
    {
        ksort($params);

        $canonicalizedQuery = '';
        foreach ($params as $key => $value) {
            if ($key === 'Signature') {
                continue;
            }
            $canonicalizedQuery .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($value);
        }
        $canonicalizedQuery = substr($canonicalizedQuery, 1);

        $stringToSign = 'GET&%2F&' . $this->percentEncode($canonicalizedQuery);
        $sign         = base64_encode(hash_hmac('sha1', $stringToSign, $this->accessKeySecret . '&', true));

        return $sign;
    }

    /**
     * URL 编码（阿里云特殊规则）
     */
    private function percentEncode($str)
    {
        $result = urlencode($str);
        $result = str_replace(['+', '*', '%7E'], ['%20', '%2A', '~'], $result);
        return $result;
    }

    /**
     * 生成随机 Nonce
     */
    private function uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * 发送 HTTP GET 请求
     */
    private function httpPost($params)
    {
        $query = http_build_query($params);
        $url   = 'https://' . $this->endpoint . '/?' . $query;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false, // 生产环境建议开启
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('cURL 请求失败：' . $error);
        }

        return $response;
    }
}
