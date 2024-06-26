<?php
/*
 * @描述:
 * @Author: Viking
 * @version: 1.0
 * @Date: 2023-03-29 00:02:52
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2024-05-23 15:32:57
 */

namespace MeaPHP;

use MeaPHP\Core\DataBase\DataBase;
use MeaPHP\Core\Tools\MID;
use MeaPHP\Core\Tools\Captcha;
// use MeaPHP\Core\Tools\Save;
use MeaPHP\Core\Tools\SecurityVerification;
use MeaPHP\Core\Tools\FormatValidation;
// use MeaPHP\Core\Tools\MoveFile;
use MeaPHP\Core\Tools\Token;
use MeaPHP\Core\Tools\Client;
use MeaPHP\Core\Tools\Encryption;

use MeaPHP\Core\Tools\File;
use MeaPHP\Core\Tools\Error;


use MeaPHP\Bootstrap\CheckUserConfig;

use MeaPHP\Core\Reply\Reply;

use MeaPHP\Core\Tools\Fotophire;

// trait Mea
class Mea
{
    protected $UserConfig;
    protected $DB;
    protected $MID;
    protected $Captcha;
    protected $Save;
    protected $SV;

    protected $MoveFile;
    protected $Token;

    protected $Encryption;
    protected $File;


    protected $Fotophire;


    protected $Client;
    protected $FV;
    protected $Error;


    protected $Reply;
    /**
     * @描述: final当前方法不能重写
     * @param {*} $UserConfig
     * @return {*}
     * @Date: 2023-04-16 10:48:50
     */
    public final function __construct($UserConfig)
    {
        // 检查用户配置；当脚本继承Mea类时，检查用户配置
        CheckUserConfig::check($UserConfig);
        $this->UserConfig = $UserConfig;

        $this->DB = DataBase::active($UserConfig);

        // 返回数据管理
        $this->Reply = Reply::active();
        //Token的管理
        $this->Token = Token::active();
        //客户端相关信息
        $this->Client = Client::active();
        //id管理
        $this->MID = MID::active();
        //图片验证码
        $this->Captcha = Captcha::active();
        //上传保存文件，移动文件等统一
        $this->File = File::active();
        //上传文件；保存到服务器
        // $this->Save = Save::active();
        //安全验证
        $this->SV = SecurityVerification::active();
        //格式验证
        $this->FV = FormatValidation::active();
        //文件移动工具
        // $this->MoveFile = MoveFile::active();
        //加密解密
        $this->Encryption = Encryption::active();
        //错误日志
        $this->Error = Error::active($UserConfig);
        //图片处理工厂：生成缩率图；手机海报
        $this->Fotophire = Fotophire::active();
    }
}

// new Mea($UserConfig);