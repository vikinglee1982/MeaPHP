<?php
/*
 * @Author: vikinglee1982 87834084@qq.com
 * @Date: 2024-08-10 16:17:01
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2024-09-13 09:58:18
 * @FilePath: \工作台\Servers\huayun_server\MeaPHP\TspApi\Free.php
 * @Description:第三方接口
 */


namespace MeaPHP\TspApi;

use MeaPHP\Core\Reply\Reply;

class TspApi
{
    private static $obj = null;

    //阻止外部克隆书库工具类
    private function __clone() {}

    //私有化构造方法初始化，禁止外部使用
    private function __construct() {}
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
     * @描述: 获取ip地址信息
     * @param {string} $ip
     * @param {bool} $doc 是否返回文档 
     * @return {*}
     * @Date: 2024-08-10 16:18:06
     */
    public  function ipToLoc(string $ip): array
    {
        $key = "5MVBZ-YDEK4-UKSUO-KXJCJ-WLWJ2-NPFVM";


        $locApi = "https://apis.map.qq.com/ws/location/v1/ip?key={$key}&ip={$ip}";
        // 发起HTTP GET请求
        $response = file_get_contents($locApi);
        $res = json_decode($response, true);

        if ($res['status'] == 0) {
            $info = $res['result']['ad_info'];
            if ($info['nation'] == '中国') {
                $province = $info['province'];
                $city = $info['city'];
                $county = $info['district'];
            } else {
                $province = $info['nation'];
                $city = null;
                $county =  null;
            }

            return Reply::To(
                'ok',
                '通过ip获取地址成功',
                [
                    'province' => $province,
                    'city' => $city,
                    'county' => $county
                ]
            );
        } else {
            return Reply::To('error', '通过ip获取地址失败');
        }
    }
}
