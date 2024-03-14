<?php
/*
 * @Author: vikinglee1982 87834084@qq.com
 * @Date: 2024-03-12 09:20:56
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2024-03-13 14:47:34
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

        $ErrorKeys = ['errCode', 'fileName', 'line', 'msg', 'tableName', 'time', 'type'];
        $MySQLKeys = ['charset', 'dbname', 'host', 'hostport', 'password', 'username'];
        $requestKeys = ['activate', 'blackList', 'whiteList'];

        self::validateConfigSection($UserConfig, 'Error', $ErrorKeys, 8000);
        self::validateConfigSection($UserConfig, 'MySQL', $MySQLKeys, 8001);
        self::validateConfigSection($UserConfig, 'Request', $requestKeys, 8002);


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
            $data['msg'] = "UserConfig的{$section}配置错误";
            $data['userconfig'] = $UserConfig;
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
