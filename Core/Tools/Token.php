<?php
/*
 * @描述: 
 * @Author: Viking
 * @version: 1.0
 * @Date: 2023-04-09 17:31:20
 * @LastEditors: Viking
 * @LastEditTime: 2023-04-13 00:29:23
 */



namespace MeaPHP\Core\Tools;





class Token 
{
    private $res = array(
        //考虑是否更改使用recode码表示返回不同的状态
        // 'recode' => 'error',
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
            $this->ContinueRenew     = $config['ContinueRenew'];
        } else {
            return false;
        }
    }
    /**
     * @描述: 生成一个token
     * @param {string} $username
     * @param {string} $psw
     * @return {*}
     * @Date: 2023-04-12 23:48:20
     */
    public function make(string $username = null, string $psw = null)
    {

        if (!$username || !$psw) {
            $this->res['status']  =  'error';
            $this->res['msg']   = '请入参:[ string $username = null, string $psw = null ]';
        } else {


            // $ip = $this->Client->getIp();
            $time = date("Y-m-d H:i:s");

            //这里如果需要加强安全程度；可以引入AES加密
            $this->res['status'] = 'ok';
            // $this->res['data'] = md5($username . $psw . $time . $ip);
            $this->res['data'] = md5($username . $psw . $time);

            // return ;
        }
        return $this->res;
    }

    //获取用户当前ip地址
    // public function getIp()
    // {

    //     if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
    //         $ip = getenv('HTTP_CLIENT_IP');
    //     } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
    //         $ip = getenv('HTTP_X_FORWARDED_FOR');
    //     } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
    //         $ip = getenv('REMOTE_ADDR');
    //     } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
    //         $ip = $_SERVER['REMOTE_ADDR'];
    //     }
    //     $userIp = preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches[0] : '';

    //     return $userIp;
    // }
}
