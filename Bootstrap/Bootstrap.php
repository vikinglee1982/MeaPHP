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
        $SingleSiteFile =  $_SERVER['DOCUMENT_ROOT'] . '/' . $file;

        $multiSiteFile = dirname($_SERVER['DOCUMENT_ROOT']) . '/' . $file;

        if (file_exists($SingleSiteFile)) {
            // echo "单站点";
            include_once $SingleSiteFile;
        } elseif (file_exists($multiSiteFile)) {
            // echo "多站点";
            include_once $multiSiteFile;
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
