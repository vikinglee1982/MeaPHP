<?php
/*
 * @描述: 获取连接服务器的客户端的用户信息
 * @Author: Viking
 * @version: 1.0
 * @Date: 2023-04-12 23:18:43
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2024-09-13 09:08:09
 */

namespace MeaPHP\Core\Tools;

use MeaPHP\Core\Reply\Reply;
use MeaPHP\TspApi\TspApi;

class Client
{

    public $res = array(
        // 'status' => 'error',
        // //只有2种状态 ok/error
        // 'data' => null,
        // //正确：返回数据
        // 'msg' => null,
        //错误：返回错误原因
    );

    protected static $Obj = null;

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
    /**
     * @描述:
     * @return {*}
     * @Date: 2023-04-12 23:48:37
     */
    public function getIp()
    {
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $userIp = preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches[0] : '';

        $this->res['status'] = 'ok';
        $this->res['sc'] = 'ok';
        $this->res['data'] = $userIp;
        // return $userIp;
        return $this->res;
    }
    /**
     * @描述: 获取用户的代理信息
     * @return {*}
     * @Date: 2023-04-12 23:56:21
     */
    //这里可以根据代理信息解析出用户的操作系统；浏览器版本；用户语言等相关信息;用户使用的是什么设备
    public function getAgent()
    {
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $OS = $_SERVER['HTTP_USER_AGENT'];
            $this->res['sc'] = 'ok';
            $this->res['status'] = 'ok';
            $this->res['data'] = $OS;
        } else {
            $this->res['status'] = 'error';
            $this->res['msg'] = "获取访客代理信息失败！";
        }
        return $this->res;
    }
}
