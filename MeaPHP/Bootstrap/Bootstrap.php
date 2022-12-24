<?php

namespace MeaPHP\Bootstrap;

use MeaPHP\Core\DataBase\DataBase;


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
//生成基础类
$DB = DataBase::start($Config);
