<?php
/*
 * @Author: vikinglee1982 87834084@qq.com
 * @Date: 2024-03-08 09:51:02
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2024-12-22 21:45:18
 * @FilePath: \工作台\Servers\huayun_server\MeaPHP\Mea.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */

namespace MeaPHP;

use MeaPHP\Core\DataBase\DataBase;
use MeaPHP\Core\Tools\MID;
use MeaPHP\Core\Tools\Captcha;
use MeaPHP\Core\Tools\SecurityVerification;
use MeaPHP\Core\Tools\FormatValidation;
use MeaPHP\Core\Tools\Token;
use MeaPHP\Core\Tools\Client;
use MeaPHP\Core\Tools\Encryption;
use MeaPHP\Core\Tools\File;
use MeaPHP\Core\Tools\Error;
use MeaPHP\Bootstrap\CheckUserConfig;
use MeaPHP\Core\Reply\Reply;
use MeaPHP\Core\Tools\Fotophire;
use MeaPHP\TspApi\TspApi;
use MeaPHP\TspApps\ImagickApp;
use MeaPHP\TspApps\LibreOfficeApp;
use MeaPHP\Core\Tools\WeChatPoster;

class Mea
{
    protected $UserConfig;
    protected $DB;
    protected $MID;
    protected $Captcha;
    protected $Token;
    protected $Encryption;
    protected $File;
    protected $SV;
    protected $FV;
    protected $Error;
    protected $Reply;
    protected $Fotophire;
    protected $Client;
    protected $TspApi;
    protected $ImagickApp;
    protected $LibreOfficeApp;
    protected $WeChatPoster;

    public final function __construct($UserConfig)
    {
        CheckUserConfig::check($UserConfig);
        $this->UserConfig = $UserConfig;

        $this->initializeServices();
    }

    private function initializeServices()
    {
        $services = [
            'DB' => [DataBase::class, 'active'],
            'Reply' => [Reply::class, 'active'],
            'Token' => [Token::class, 'active'],
            'Client' => [Client::class, 'active'],
            'MID' => [MID::class, 'active'],
            'Captcha' => [Captcha::class, 'active'],
            'File' => [File::class, 'active'],
            'SV' => [SecurityVerification::class, 'active'],
            'FV' => [FormatValidation::class, 'active'],
            'Encryption' => [Encryption::class, 'active'],
            'Error' => [Error::class, 'active'],
            'Fotophire' => [Fotophire::class, 'active'],
            'TspApi' => [TspApi::class, 'active'],
            'ImagickApp' => [ImagickApp::class, 'active'],
            'LibreOfficeApp' => [LibreOfficeApp::class, 'active'],
            'WeChatPoster' => [WeChatPoster::class, 'active'],
        ];
        try {
            $this->DB = DataBase::active($this->UserConfig);
        } catch (\Throwable $e) {
            $this->handleException($e);
        }

        foreach ($services as $property => [$class, $method]) {
            try {
                $this->$property = $class::$method($this->UserConfig ?? []);
            } catch (\Throwable $e) {
                $this->handleException($e);
            }
        }
    }

    private function handleException(\Throwable $e)
    {
        echo $e->getMessage(); // 更好的做法是记录日志而不是直接输出错误消息
    }
}
