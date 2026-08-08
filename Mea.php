<?php
/*
 * @Author: vikinglee1982 87834084@qq.com
 * @Date: 2024-03-08 09:51:02
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2026-05-28 15:57:45
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
use MeaPHP\TspApi\AlySMS;
use MeaPHP\TspApps\ImagickApp;
use MeaPHP\TspApps\LibreOfficeApp;
use MeaPHP\Core\Tools\WeChatPoster;
use MeaPHP\Core\Tools\WeChatApi;
use MeaPHP\Core\Tools\UrlFetcher;
use MeaPHP\Core\Tools\Directory;

class Mea
{
    /** @var array 用户配置信息 */
    protected $UserConfig;

    /** @var DataBase|null 数据库实例 */
    protected $DB;

    /** @var MID|null 编号工具 */
    protected $MID;

    /** @var Captcha|null 验证码工具 */
    protected $Captcha;

    /** @var Token|null Token管理工具 */
    protected $Token;

    /** @var Encryption|null 加密工具 */
    protected $Encryption;

    /** @var File|null 文件处理工具 */
    protected $File;

    /** @var SecurityVerification|null 安全验证工具 */
    protected $SV;

    /** @var FormatValidation|null 格式验证工具 */
    protected $FV;

    /** @var Error|null 错误处理工具 */
    protected $Error;

    /** @var Reply|null 响应处理工具 */
    protected $Reply;

    /** @var Fotophire|null 图片处理服务 */
    protected $Fotophire;

    /** @var Client|null 客户端连接工具 */
    protected $Client;

    /** @var TspApi|null 第三方 API接口 */
    protected $TspApi;

    /** @var AlySMS|null 阿里云短信服务*/
    protected $AlySMS;

    /** @var ImagickApp|null Imagick应用 */
    protected $ImagickApp;

    /** @var LibreOfficeApp|null LibreOffice应用 */
    protected $LibreOfficeApp;

    /** @var WeChatPoster|null 微信海报工具 */
    protected $WeChatPoster;

    /** @var WeChatApi|null 微信API工具 */
    protected $WeChatApi;

    /** @var UrlFetcher|null URL抓取工具 */
    protected $UrlFetcher;

    /** @var Directory|null 目录处理工具 */
    protected $Directory;

    public final function __construct(array $UserConfig)
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
            'AlySMS' => [AlySMS::class, 'active'],
            'ImagickApp' => [ImagickApp::class, 'active'],
            'LibreOfficeApp' => [LibreOfficeApp::class, 'active'],
            'WeChatPoster' => [WeChatPoster::class, 'active'],
            'WeChatApi' => [WeChatApi::class, 'active'],
            'UrlFetcher' => [UrlFetcher::class, 'active'],
            'Directory' => [Directory::class, 'active'],
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
        error_log('[Mea] Service init error: ' . $e->getMessage());
    }
}
