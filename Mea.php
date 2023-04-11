<?php

namespace MeaPHP;

use MeaPHP\Core\DataBase\DataBase;
use MeaPHP\Core\Tools\MID;
use MeaPHP\Core\Tools\Captcha;
use MeaPHP\Core\Tools\Save;
use MeaPHP\Core\Tools\SecurityVerification;
use MeaPHP\Core\Tools\FormatValidation;
use MeaPHP\Core\Tools\MoveFile;
use MeaPHP\Core\Tools\Token;

class Mea
{
    protected $DB;
    protected $MID;
    protected $Captcha;
    protected $Save;
    protected $SV;
    public $FV;
    protected $MoveFile;
    protected $UserConfig;
    protected $Token;

    public final function __construct($UserConfig)
    {
        $this->UserConfig = $UserConfig;

        $this->DB = DataBase::active($UserConfig);

        //Token的管理
        $this->Token = Token::active($UserConfig);
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
    }
}
