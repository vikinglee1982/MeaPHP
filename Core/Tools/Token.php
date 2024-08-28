<?php
/*
 * @描述: token管理
 * @Author: Viking
 * @version: 1.0
 * @Date: 2023-04-09 17:31:20
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2024-08-26 15:23:21
 */



namespace MeaPHP\Core\Tools;

use MeaPHP\Core\DataBase\DataBase;


class Token
{


    private $Client;
    // use Mea;
    // private $M;
    public $res = array(
        // 'status' => 'error',
        // //只有2种状态 ok/error
        // 'data' => null,
        // //正确：返回数据
        // 'msg' => null,
        //错误：返回错误原因
    );


    public static $TokenObj;


    //内部产生静态对象
    public static function active()
    {
        // var_dump($config);
        // echo "开始创建<hr>";
        if (!self::$TokenObj instanceof self) {
            // echo "不存在，创建了<hr>";
            //如果不存在，创建保存
            self::$TokenObj = new self();
        }
        return self::$TokenObj;
    }
    //阻止外部克隆书库工具类
    private function __clone()
    {
    }

    //构造方法初始化，属性赋值，准备连接
    private function __construct()
    {

        $this->Client =  Client::active();
    }
    /**
     * @描述: 生成一个token
     * @param {string} $eUser
     * @param {string} $psw
     * @return {string} $token
     * @Date: 2023-04-12 23:48:20
     */
    public function make(string $eUser = null, string $psw = null)
    {

        if (!$eUser) {

            $this->res['status'] = 'error';
            $this->res['sc'] = 'error';
            $this->res['msg'] = "缺少用户id或用户名";
        } elseif (!$psw) {
            $this->res['status'] = 'error';
            $this->res['sc'] = 'error';
            $this->res['msg'] = "缺少密码";
        } else {
            //使用工具类获取用户的ip地址
            $getip = $this->Client->getIp();
            if ($getip['status'] == 'ok') {
                $ip = $getip['data'];
                $time = date("Y-m-d H:i:s");
                //这里如果需要加强安全程度；可以引入AES加密
                $this->res['status'] = 'ok';
                $this->res['sc'] = 'ok';
                $this->res['data'] =  md5($eUser . $psw . $time . $ip);
            } else {
                $this->res['status'] = 'error';
                $this->res['sc'] = 'error';
                $this->res['msg'] = "不能获取到用户ip";
            }
        }
        return $this->res;
    }
    /**
     * @description: 用户登陆以后更新token
     * @return {$newToken}
     */
    public function renewal(
        string $eUser = null,
        string $ip = null,
        string $agent = null
    ): array {

        if (!$eUser) {
            $this->res['status'] = 'error';
            $this->res['msg'] = '缺少参数1:$eUser';
        } elseif (!$ip) {

            $this->res['status'] = 'error';
            $this->res['msg'] = '缺少参数2:$ip';
        } elseif (!$agent) {
            $this->res['status'] = 'error';
            $this->res['msg'] = '缺少参数3:$agent';
        } else {

            $time = date("Y-m-d H:i:s");
            $newToken = md5($eUser . $agent . $time . $ip);

            $this->res['status'] = 'ok';
            $this->res['data'] =  $newToken;
        }


        return $this->res;
    }

    // $this->res['tokenDB'] = $this->DB->selectOne("SELECT * FROM lzb_user_keeper ");
}
