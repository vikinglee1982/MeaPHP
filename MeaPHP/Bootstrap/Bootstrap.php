<?php

namespace MeaPHP\Bootstrap;

use MeaPHP\Core\DataBase\DataBase;
use MeaPHP\Core\Tools\MID;

use MeaPHP\Core\Tools\Captcha;

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
        // echo '============' . $file . '=========';
        if (file_exists($file)) {
            // echo "<hr>";
            // echo $file;
            // echo "加载了：" . $file;
            // echo "<hr>";
            include_once $file;
        } else {
            var_dump([
                'errorfile' => 'Bootsrtap.php',
                'errorMessage' => "自动加载:[{$file}] 文件失败",
            ]);
        }
    }
}
Bootstrap::autoLoad();
//生成基础类
$DB = DataBase::start($Config);
//
$MID = MID::start();

//
$Captcha = Captcha::start();
