<?php

namespace MeaPHP\Config;

date_default_timezone_set("PRC");
class Config
{
    private $dbkey = array(

        //规定主机名或 IP 地址。
        'host'           => 'localhost',

        // 端口
        'hostport'       => '3306',

        //规定 MySQL 用户名。
        'username'       => 'taoa_api',

        //规定 MySQL 密码。
        'password'       => 'Taoa_Api_051206',

        //规定使用的数据库。
        'dbname'         => 'TAOA',

        //数据库编码默认采用utf8
        // 'charset'  => 'utf8mb4 ',

        //程序是否上线，true将不能看见返回错误信息
        'online'         => true,

        //域名或固定ip地址前缀,数组形式，可以设置多个，禁止盗用API或者使用url直接访问api接口
        //请注意地址书写规范
        //*:代表所有都可以访问
        'url'            => array('*'),

        //当前域名的报头

        'http_protocol'  => 'http://',

        //是否使用访问续约管理
        'ContinueRenew'  => true,
        //用户访问续约
        'Continue'       => array(
            //访问控制数据库表名称
            'tableName'           => 'taoa_admin',
            //续约周期时间（秒）
            'expiration'          => 0,
            //过期时间列名称（字段名称）
            'tokenExptimeColName' => 'token_exptime',
            //token列名称
            'tokenColName'        => 'token',
            //用户设备id列名称
            'deviceIdColName'     => 'device_id',
            //用户过期时需要清除的数据
            'clearColName'        => array('token', 'token_exptime', 'login_time', 'login_ip', 'device_id'),
        ),
        //微信小程序登录所需要的
        'wxAppid'        => 'wxc9caf5a386cef1ba',
        'wxAppSecret'    => '985a512573ae76dafe282a78fe9d6c52',
        //用户储存文件的基础路径；用户储存的文件全部都在这个文件夹下面
        // 'UserBasicsPath' => $_SERVER['DOCUMENT_ROOT'] . '/Resource',
    );
    public function __construct()
    {
        return $this->dbkey;
    }
}
