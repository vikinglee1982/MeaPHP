<?php
/*
 * @Author: vikinglee1982 87834084@qq.com
 * @Date: 2024-03-12 09:20:56
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2024-04-23 15:47:54
 * @FilePath: \工作台\Servers\huayun_server\MeaPHP\Bootstrap\CheckUserConifg.php
 * @Description: 检查用户配置并添加默认配置
 */

namespace MeaPHP\Bootstrap;

use MeaPHP\Export\Export;

class CheckUserConfig
{
    /**
     * 检查用户配置并添加默认配置
     */
    public static function check($UserConfig)
    {

        //配置中需要检查的配置需要在这里注册
        $ConfigKeys = ['Debug', 'MySQL', 'Error', 'Request'];
        $ErrorKeys = ['errCode', 'fileName', 'line', 'msg', 'tableName', 'time', 'type'];
        $MySQLKeys = ['charset', 'dbname', 'host', 'hostport', 'password', 'username'];
        $requestKeys = ['activate', 'blackList', 'whiteList'];

        self::validateConfigKeys($UserConfig, $ConfigKeys, 8000);
        self::validateConfigDebug($UserConfig, 'Debug',  8001);
        self::validateConfigSection($UserConfig, 'MySQL', $MySQLKeys, 8002);
        self::validateConfigSection($UserConfig, 'Request', $requestKeys, 8003);
        self::validateConfigSection($UserConfig, 'Error', $ErrorKeys, 8004);



        // $CF = self::defaultGlobalVariable($UserConfig);

    }
    /**
     * 填充不需要用户填写，自动生成的配置（暂时没有引用）
     */
    public static function defaultGlobalVariable($UserConfig): array
    {

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
        return [];
    }
    /**
     * 验证配置是否符合要求
     */
    function validateConfigSection($UserConfig, $section, $requiredKeys, $errorCode)
    {

        if (!is_array($UserConfig[$section]) || array_diff_key(array_flip($requiredKeys), $UserConfig[$section])) {
            $data['msg'] = "UserConfig配置错误-{$section}";
            $data['UserConfig'] = $UserConfig;
            self::endScript($errorCode, $data);
        }
    }
    private static function validateConfigDebug($UserConfig, $section, $errorCode)
    {
        if (!is_bool($UserConfig[$section])) {
            $data['msg'] = "UserConfig配置错误-{$section}";
            $data['UserConfig'] = $UserConfig;
            self::endScript($errorCode, $data);
        }
    }
    // private static function validateConfigKeys($UserConfig, $section, $requiredKeys, $errorCode)
    // {
    private static function validateConfigKeys($UserConfig,  $requiredKeys, $errorCode)
    {


        if (!is_array($UserConfig) || array_diff_key(array_flip($requiredKeys), $UserConfig)) {
            $data['msg'] = "UserConfig配置参数不完整";
            $data['UserConfig'] = $UserConfig;
            self::endScript($errorCode, $data);
        }
    }

    /**
     * 脚本结束，并输出返回信息
     */
    public static function endScript($recode, $data): void
    {
        $Export = Export::active();

        $Export::send([], $recode, $data);

        exit;
    }
}
