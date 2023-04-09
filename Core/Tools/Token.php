<?php

/**
 * 2023年4月修订
 * token验证；
 * token 生成
 * 续约
 * 清除
 */
class Token
{
    protected $TokenObj;

   $'ContinueRenew' => [
        //是否开启使用
        'enable' => true,
        //请求接口的头信息中的token名称；自定义请修改
        'headerTokenName'=>'token',
        //续约周期时间（秒）,如果是0表示永远不过期
        'expiration'          => 0,
        //涉及需要修改数据库的字段
        'columnName' => [
            'token' => 'token',
            'indate' => 'token_indate',
            'ip' => 'user_ip',
            'loginTime' => 'login_time',
        ],
    ],

    //阻止外部克隆书库工具类
    private function __clone()
    {
    }
    //内部产生静态对象
    public static function active($config)
    {
        // var_dump($dbkey);
        if (!self::$TokenObj instanceof self) {
            //如果不存在，创建保存
            self::$TokenObj = new self($config);
        }
        return self::$TokenObj;
    }
    //构造方法初始化，属性赋值，准备连接
    private function __construct($config)
    {
        if ($config) {
            $this->host     = $config['MySQL']['host'];
            $this->username = $config['MySQL']['username'];
            $this->password = $config['MySQL']['password'];
            $this->dbname   = $config['MySQL']['dbname'];
            $this->hostport = $config['MySQL']['hostport'];
            $this->charset =  $config['MySQL']['charset'];
            $this->online   = $config['online'];

            //调用类内连接数据库方法
            // $this->connect();
        } else {
            return false;
        }
    }
}
