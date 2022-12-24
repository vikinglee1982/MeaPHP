<?php

namespace MeaPHP\AutoLoad;

use MeaPHP\Core\DataBase\DataBase;
use MeaPHP\Config\Config;

class Bootstrap
{
    public static function autoLoad()
    {
        spl_autoload_register([new self(), 'classPath']);
    }
    public function classPath(string $class)
    {
        $file = str_replace('\\', '/', $class) . '.php';
        $file =  $_SERVER['DOCUMENT_ROOT'] . '/' . $file;

        if (file_exists($file)) {
            echo "<hr>";
            // echo $file;

            echo "加载了：" . $file;
            echo "<hr>";
            include_once $file;
        } else {
            echo "不存在";
        }
    }
}
Bootstrap::autoLoad();

$dbkey = new Config();
$con = array(
    //规定主机名或 IP 地址。
    'host'           => 'localhost',

    // 端口
    'hostport'       => '3306',

    //规定 MySQL 用户名。
    'username'       => 'vzone_api',

    //规定 MySQL 密码。
    'password'       => 'Vzone_Api_051206',

    //规定使用的数据库。
    'dbname'         => 'Vzone',

    //数据库编码默认采用utf8
    // 'charset'  => 'utf8mb4 ',


);
echo "<hr>";
var_dump($dbkey);
echo "<hr>";
echo "开始加载数据库类";
echo "<hr>";
$DB = DataBase::start($con);

echo "数据库类加载了";
