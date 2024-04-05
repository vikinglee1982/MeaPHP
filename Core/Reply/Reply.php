<?php
/*
 * @Author: vikinglee1982 87834084@qq.com
 * @Date: 2024-04-05 17:34:18
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2024-04-05 18:16:37
 * @FilePath: \工作台\Servers\huayun_server\MeaPHP\Core\Reply\Reply.php
 * @Description: tools工具类的返回类；这里定义了返回类的方法及返回内容；统一管理
 */



namespace MeaPHP\Core\Reply;

class Reply
{

    public static function To(string $state, string $msg = '', array $data = []): array
    {

        if ($state != 'ok' && $state != 'err') {
            throw new \InvalidArgumentException("Invalid state code: Reply类返回状态码错误-'$state'");
        }
        //$msg为空时删除$res中的msg

        //$data为空时删除$res中的data
        $res = [
            'sc' => $state,
            'msg' => $msg ?: null,
            'data' => $data ?: null,
        ];

        return $res;
    }
}
