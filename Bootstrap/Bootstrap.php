<?php

namespace MeaPHP\Bootstrap;

use MeaPHP\Core\DataBase\DataBase;

use MeaPHP\Core\Tools\MID;
use MeaPHP\Core\Tools\Captcha;
use MeaPHP\Core\Tools\Save;
use MeaPHP\Core\Tools\SecurityVerification;
use MeaPHP\Core\Tools\FormatValidation;
use MeaPHP\Core\Tools\MoveFile;
use MeaPHP\Mea;


class Bootstrap
{

    public static function autoLoad()
    {
        spl_autoload_register([new self(), 'classPath']);
    }
    public function classPath(string $class)
    {
        $file = str_replace('\\', '/', $class) . '.php';
        // $SingleSiteFile =  $_SERVER['DOCUMENT_ROOT'] . '/' . $file;
        $CoreClassFile = dirname($_SERVER['DOCUMENT_ROOT']) . '/' . $file;
        // echo $CoreClassFile . '<br>';

        //这里注册核心类的类名称；用户使用当前类名称时提示用户类名已被占用；不能使用
        $coreClass = ['DataBase', 'MID', 'Captcha', 'Save', 'SecurityVerification', 'MoveFile', 'FormatValidation'];

        if (in_array($class, $coreClass)) {
            var_dump([
                'errorfile' => 'Bootsrtap.php',
                'errorMessage' => "自动加载:[{$file}] 文件失败;当前类名称已经被MeaPHP占用；",
            ]);
            die;
        }

        if (file_exists($CoreClassFile)) {
            // echo "框架自己的核心工具类,第一个加载";
            // echo $CoreClassFile . "<br>";
            require $CoreClassFile;
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
$DB = DataBase::active($UserConfig);

//id管理
$MID = MID::active();
//图片验证码
$Captcha = Captcha::active();
//上传文件；保存到服务器
$Save = Save::active();
//安全验证
$SV = SecurityVerification::active();
//格式验证
$FV = FormatValidation::active();
//文件移动工具
$MoveFile = MoveFile::active();
