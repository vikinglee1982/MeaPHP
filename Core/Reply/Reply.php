<?php
/*
 * @Author: vikinglee1982 87834084@qq.com
 * @Date: 2024-04-05 17:34:18
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2024-04-10 16:56:07
 * @FilePath: \工作台\Servers\huayun_server\MeaPHP\Core\Reply\Reply.php
 * @Description: tools工具类的返回类；这里定义了返回类的方法及返回内容；统一管理
 */



namespace MeaPHP\Core\Reply;

class Reply
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
    public static function To(string $state, string $msg = '', array $data = []): array
    {

        if ($state != 'ok' && $state != 'err' && $state >= 2000 && $state <= 9000) {
            throw new \InvalidArgumentException("Invalid state code: Reply类返回状态码错误-'$state'");
        }
        //$msg为空时删除$res中的msg

        //$data为空时删除$res中的data
        $res = [
            'sc' => $state,
            'msg' => $msg ?: null,
            'data' => $data ?: null,
        ];

        return $res;
    }
    /**
     * @description:返回外网
     * @param {*} $state
     * @param {*} $msg
     * @param {*} $data
     * @return {*}
     */
    // public static function Send(int $recode, string $msg = '', array $data = []): array
    // {

    //     if ($recode < 2000 && $recode > 9000) {
    //         throw new \InvalidArgumentException("Invalid state code: Reply类返回状态码错误-'$recode'");
    //     }
    //     //$msg为空时删除$res中的msg
    //     $data['ms'] = date('Y-m-d h:i:s', time());
    //     //$data为空时删除$res中的data
    //     $res = [
    //         'recode' => $recode,
    //         'msg' => $msg ?: null,
    //         'data' => $data ?: null,
    //     ];

    //     return $res;
    // }
}
