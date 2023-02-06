<?php

namespace MeaPHP\Bootstrap;

use MeaPHP\Core\DataBase\DataBase;

use MeaPHP\Core\Tools\MID;
use MeaPHP\Core\Tools\Captcha;
use MeaPHP\Core\Tools\Save;
use MeaPHP\Core\Tools\Verify;

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
//生成数据库操作基础类
$DB = DataBase::active($Config);
//id管理
$MID = MID::active();
//图片验证码
$Captcha = Captcha::active();
//上传文件；保存到服务器
$Save = Save::active();
//安全验证
$RV = Verify::active();
//添加
