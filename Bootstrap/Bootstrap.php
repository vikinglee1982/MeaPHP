<?php

namespace MeaPHP\Bootstrap;

use MeaPHP\Core\DataBase\DataBase;

use MeaPHP\Core\Tools\MID;
use MeaPHP\Core\Tools\Captcha;
use MeaPHP\Core\Tools\Save;
use MeaPHP\Core\Tools\Verify;
use MeaPHP\Core\Tools\MoveFile;


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

        $siteObjectClassFile = $_SERVER['DOCUMENT_ROOT'] . '/Api/ObjectClass/' . $class . '.php';
        // echo "<br>";
        // echo ($_SERVER['DOCUMENT_ROOT'] . '/Api/ObjectClass/' . $class . '.php');
        // echo "<br>";
        // echo $class;
        // echo "-----------------------";
        // echo "<br>";

        if (file_exists($siteObjectClassFile)) {
            // echo "站点自己的对象类；面向对象";
            require $siteObjectClassFile;
        } elseif (file_exists($CoreClassFile)) {
            // echo "框架自己的核心工具类";
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
$RV = Verify::active();
//文件移动工具
$MoveFile = MoveFile::active();
