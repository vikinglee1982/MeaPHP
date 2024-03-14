<?php

/*
 * @Author: vikinglee1982 87834084@qq.com
 * @Date: 2024-03-10 16:20:37
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2024-03-13 14:54:20
 * @FilePath: \工作台\Servers\huayun_server\MeaPHP\Bootstrap\RequestControl.php
 * @Description: 通过Bootstrap调用当前类 ，用于控制用户设置的访问控制
 */

namespace MeaPHP\Bootstrap;

use MeaPHP\Core\Tools\Client;

use MeaPHP\Export\Export;

class RequestControl
{
    /**
     * @description: 用户请求控制，黑白名单
     */
    public static  function check($UserConfig)
    {  //黑白名单的访问控制

        $Client = Client::active();
        $ip = $Client->getIp();

        $Export = Export::active();

        // $data['ip'] = $ip;

        $requestMode =  $UserConfig['request']['activate'];

        if ($requestMode == 'w') {

            if (!in_array($ip, $UserConfig['request']['whiteList'])) {

                $recode = 3000;
                $data['msg'] = '您不在访问白名单内，禁止访问';

                $Export::send([], $recode, $data);
                //结束脚本运行
                exit;
            }
        } else if ($requestMode == 'b') {

            if (in_array($ip, $UserConfig['request']['blackList'])) {

                $recode = 3000;
                $data['msg'] = '您已经被加入黑名单,禁止访问,如有疑问请联系管理员';

                $Export::send([], $recode, $data);
                 //结束脚本运行
                exit;
            }
        }
       
    }
}
