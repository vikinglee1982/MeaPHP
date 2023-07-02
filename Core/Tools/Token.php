<?php
/*
 * @描述: token管理
 * @Author: Viking
 * @version: 1.0
 * @Date: 2023-04-09 17:31:20
 * @LastEditors: vikinglee1982 750820181@qq.com
 * @LastEditTime: 2023-07-02 16:09:27
 */



namespace MeaPHP\Core\Tools;

use MeaPHP\Core\DataBase\DataBase;


class Token
{

    private $DB;
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
    protected  $ContinueRenew =  [

        //续约周期时间（秒）,如果是0表示永远不过期
        'expiration'          => 0,
        //涉及需要修改数据库的字段key="表名称"；value=“对应表中的字段名”
        //多个用户表；添加多个同样数据格式的同级数组
        'lzb_user_keeper' => [
            'token' => 'token',
            'indate' => 'token_indate',
            'ip' => 'user_ip',
            'loginTime' => 'login_time',
        ],
    ];


    //内部产生静态对象
    public static function active($config)
    {
        // var_dump($config);
        // echo "开始创建<hr>";
        if (!self::$TokenObj instanceof self) {
            // echo "不存在，创建了<hr>";
            //如果不存在，创建保存
            self::$TokenObj = new self($config);
        }
        return self::$TokenObj;
    }
    //阻止外部克隆书库工具类
    private function __clone()
    {
    }

    //构造方法初始化，属性赋值，准备连接
    private function __construct($config)
    {
        if ($config) {
            //加载所需要使用的工具类对象
            $this->DB =  DataBase::active($config);
            $this->Client =  Client::active();
            $this->ContinueRenew     = $config['ContinueRenew'];
        } else {
            return false;
        }
    }
    /**
     * @描述: 生成一个token
     * @param {string} $username
     * @param {string} $psw
     * @return {string} $token
     * @Date: 2023-04-12 23:48:20
     */
    public function make(string $username = null, string $psw = null)
    {

        if (!$username) {

            $this->res['status'] = 'error';
            $this->res['msg'] = "缺少用户名";
        } elseif (!$psw) {
            $this->res['status'] = 'error';
            $this->res['msg'] = "缺少密码";
        } else {
            //使用工具类获取用户的ip地址
            $getip = $this->Client->getIp();
            if ($getip['status'] == 'ok') {
                $ip = $getip['data'];
                $time = date("Y-m-d H:i:s");
                //这里如果需要加强安全程度；可以引入AES加密
                $this->res['status'] = 'ok';
                $this->res['data'] =  md5($username . $psw . $time . $ip);
            } else {
                $this->res['status'] = 'error';
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
        string $username = null,
        string $ip = null,
        string $agent = null
    ): array {

        if (!$username) {
            $this->res['status'] = 'error';
            $this->res['msg'] = '缺少参数1:$username';
        } elseif (!$ip) {

            $this->res['status'] = 'error';
            $this->res['msg'] = '缺少参数2:$ip';
        } elseif (!$agent) {
            $this->res['status'] = 'error';
            $this->res['msg'] = '缺少参数3:$agent';
        } else {

            $time = date("Y-m-d H:i:s");
            $newToken = md5($username . $agent . $time . $ip);

            $this->res['status'] = 'ok';
            $this->res['data'] =  $newToken;
        }


        return $this->res;
    }

    // $this->res['tokenDB'] = $this->DB->selectOne("SELECT * FROM lzb_user_keeper ");
}
