<?php
/*
 * @描述:
 * @Author: Viking
 * @version: 1.0
 * @Date: 2023-03-05 17:53:22
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2024-03-13 14:56:52
 */

namespace MeaPHP\Bootstrap;

use MeaPHP\Core\DataBase\DataBase;

use MeaPHP\Core\Tools\MID;
use MeaPHP\Core\Tools\Captcha;
use MeaPHP\Core\Tools\Save;
use MeaPHP\Core\Tools\SecurityVerification;
use MeaPHP\Core\Tools\FormatValidation;
use MeaPHP\Core\Tools\MoveFile;
use MeaPHP\Core\Tools\Token;
use MeaPHP\Core\Tools\Client;
use MeaPHP\Core\Tools\Encryption;
use MeaPHP\Core\Tools\Error;
use MeaPHP\Core\Tools\ImageFactory;
// use MeaPHP\Mea;

// use MeaPHP\Mea;

use MeaPHP\Bootstrap\RequestControl;
use MeaPHP\Bootstrap\CheckUserConfig;
use MeaPHP\Export\Export;
use MeaPHP\Bootstrap\Header;


class Bootstrap
{
    /**
     * @description: 自动加载
     */
    public static function autoLoad()
    {
        // $RC = new RequestControl($UserConfig);

        spl_autoload_register([new self(), 'classPath']);
    }
    public function classPath(string $class)
    {
        $file = str_replace('\\', '/', $class) . '.php';
        // $SingleSiteFile =  $_SERVER['DOCUMENT_ROOT'] . '/' . $file;
        $CoreClassFile = dirname($_SERVER['DOCUMENT_ROOT']) . '/' . $file;
        // echo $CoreClassFile . '<br>';

        //这里注册核心类的类名称；用户使用当前类名称时提示用户类名已被占用；不能使用(名称加上一个Mea前缀，减小对用户定义类的影响)
        $coreClass = ['DataBase', 'MID', 'Captcha', 'Save', 'SecurityVerification', 'MoveFile', 'FormatValidation', 'Mea', 'Token', 'Client', 'Encryption', 'Error', 'ImageFactory', 'RequestControl', 'Export', 'CheckUserConfig', 'Header'];

        if (in_array($class, $coreClass)) {
            var_dump([
                'errorfile' => 'Bootsrtap.php',
                'errorMessage' => "自动加载:[{$file}] 文件失败;当前类名称已经被MeaPHP占用;",
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
// 这里要检查用户config 的参数是否完全填写；缺少参数可能会导致后续异常
if ($UserConfig['errLog']['enabled']) {
    $err = [
        'time' => date('Y-m-d H:i:s'),
        'fileName' => $_SERVER['SCRIPT_NAME'],
        'lineNum' => __LINE__,
        'errCode' => '',
        'type' => '',
        'msg' => '',
    ];
}



Bootstrap::autoLoad($UserConfig);
Header::set();
$Export = Export::active();
CheckUserConfig::check($UserConfig);
RequestControl::check($UserConfig);

//安全验证
$SV = SecurityVerification::active();

// if (!$RC->checkUserConfig()) {
// } elseif (!$RC->checkRequest()) {
// } elseif (!$RC->setHeader()) {
// } else {

// $Mea = new Mea($UserConfig);
// $data['bootstrapConfig'] = $UserConfig;
//生成数据库操作基础类
// $DB = DataBase::active($UserConfig);

// //token管理【验证，生成，续约，剔除】

// $Token = Token::active($UserConfig);

// $Error = Error::active($UserConfig);
// //客户端信息
// $Client = Client::active();

// //id管理
// $MID = MID::active();
// //图片验证码
// $Captcha = Captcha::active();
// //上传文件；保存到服务器
// $Save = Save::active();

// //格式验证
// $FV = FormatValidation::active();
// //文件移动工具
// $MoveFile = MoveFile::active();
// //加密解密
// $Encryption = Encryption::active();

// //图片工厂处理
// $ImageFactory = ImageFactory::active();

//api输出类

// }
