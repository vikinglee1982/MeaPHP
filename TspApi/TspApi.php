<?php
/*
 * @Author: vikinglee1982 87834084@qq.com
 * @Date: 2024-08-10 16:17:01
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2024-08-10 17:02:12
 * @FilePath: \工作台\Servers\huayun_server\MeaPHP\TspApi\Free.php
 * @Description:第三方接口
 */


namespace MeaPHP\TspApi;

class TspApi
{
    private static $obj = null;

    //阻止外部克隆书库工具类
    private function __clone()
    {
    }

    //私有化构造方法初始化，禁止外部使用
    private function __construct()
    {
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
     * @描述: 获取ip地址信息
     * @param {string} $ip
     * @param {bool} $doc 是否返回文档
     * @return {*}
     * @Date: 2024-08-10 16:18:06
     */
    public  function ipToLoc(string $ip, bool $doc = false): string
    {
        if ($doc) {
            $sdk = [
                'adcode' => [
                    'a' => "行政区划代码/身份证前六位区域码；例如[130284]",
                    'c' => "城市中文名称；例如[唐山]",
                    'i' => true,
                    'n' => "省市名称;例如[河北-唐山]",
                    'o' => "省市县及运营商信息;例如[河北省唐山市滦州 - 移动]",
                    'p' => "省名称;例如[河北]",
                    'r' => "省市名称;例如[河北-唐山]",
                ],
                'code' => 200,
                'ipdata' => [
                    'info1' => "河北省",
                    'info2' => "唐山市",
                    'info3' => "滦州",
                    'isp' => "移动",
                ],
                'ipinfo' => [
                    'cnip' => true,
                    'text' => "183.198.219.196",
                    'type' => "ipv4",
                ],
                'msg' => "SUCCESS",
                'time' => 1723279324,
                'tips' => "接口由VORE-API(https://api.vore.top/)免费提供",
            ];
            return json_encode($sdk);
        } else {
            return "https://api.vore.top/api/IPdata?ip={$ip}";
        }
    }
}
