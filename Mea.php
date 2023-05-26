<?php
/*
 * @描述: 
 * @Author: Viking
 * @version: 1.0
 * @Date: 2023-03-29 00:02:52
 * @LastEditors: Viking
 * @LastEditTime: 2023-05-04 23:33:14
 */

namespace MeaPHP;

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

// trait Mea
class Mea
{
    protected $UserConfig;
    protected $DB;
    protected $MID;
    protected $Captcha;
    protected $Save;
    protected $SV;
    public $FV;
    protected $MoveFile;
    protected $Token;
    protected $Client;
    protected $Encryption;
    /**
     * @描述: final当前方法不能重写
     * @param {*} $UserConfig
     * @return {*}
     * @Date: 2023-04-16 10:48:50
     */
    public final function __construct($UserConfig)
    {
        $this->UserConfig = $UserConfig;

        $this->DB = DataBase::active($UserConfig);

        //Token的管理
        $this->Token = Token::active($UserConfig);
        //客户端相关信息
        $this->Client = Client::active();
        //id管理
        $this->MID = MID::active();
        //图片验证码
        $this->Captcha = Captcha::active();
        //上传文件；保存到服务器
        $this->Save = Save::active();
        //安全验证
        $this->SV = SecurityVerification::active();
        //格式验证
        $this->FV = FormatValidation::active();
        //文件移动工具
        $this->MoveFile = MoveFile::active();
        //加密解密
        $this->Encryption = Encryption::active();
    }
}
