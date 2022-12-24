<?php

namespace MeaPHP;

use MeaPHP\AutoLoad;

date_default_timezone_set("PRC");
$config = array(

    //规定主机名或 IP 地址。
    'host'           => 'localhost',
    // 端口
    'hostport'       => '3306',
    //规定 MySQL 用户名。
    'username'       => 'viking',
    //规定 MySQL 密码。
    'password'       => 'LK_051206_ls',
    //规定使用的数据库。
    'dbname'         => 'agrirus',
    //数据库编码默认采用utf8
    // 'charset'        => 'utf8mb4 ',
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
        'tableName'           => 'a_user',
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
    //管理员用户的续约管理
    'Admin'       => array(
        //访问控制数据库表名称
        'tableName'           => 'a_admin',
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
    //错误信息储存
    'ErrorMsg' => array(
        //储存错误的表
        'tableName' => 'errmsg',
        //错误信息的字段名称：错误发生时间
        'dtime' => 'dtime',
        //错误信息的字段名称：错误发生的文件路径
        'file' => 'filepath',
        //错误信息的字段名称：报错的行数
        'line' => 'codeline',
        //错误信息的字段名称：手动添加的错误信息
        'message' => 'message',
        //错误信息的字段名称：返回的错误代码信息
        'returncodes' => 'res',
    ),
    //邮件发送
    'email' => array(
        //使用了那个邮箱代发
        'host' => 'smtp.qq.com',
        //设置发件人昵称 显示在收件人邮件的发件人邮箱地址前的发件人姓名
        'FromName' => 'www.agrirus.com',
        //代发邮箱的用户名
        'emailID' => '750820181@qq.com',
        //代发邮箱的授权码密码
        'emailPsw' => 'scykgbqvbhjzbcgi',
    ),
    //阿里云短信
    'aliyunSms' => array(
        // 您的 AccessKey ID
        "accessKeyId" => 'LTAI5t7RC6ow1jKqridTg4x7',
        // 您的 AccessKey Secret
        "accessKeySecret" => '2pU2panO2tMGZ8dfz7A0dszkhtBkuB'
    ),
);
AutoLoad\AutoLoad::start($config);
