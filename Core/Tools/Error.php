<?php
/*
 * @Author: vikinglee1982 750820181@qq.com
 * @Date: 2023-11-03 14:53:13
 * @LastEditors: vikinglee1982 750820181@qq.com
 * @LastEditTime: 2023-11-03 17:03:38
 * @FilePath: \工作台\Servers\lzkj_server\MeaPHP\Core\Tools\Error.php
 * @Description: 错误日志记录工具类
 */

namespace MeaPHP\Core\Tools;



class Error
{
    private static $obj = null;

    private $errdb = [
        'table' => 12121,
        'type' => null,
        //发生的时间
        'time' => null,
        //文件名称
        'fileName' => null,
        //文件行数
        'line' => null,
        //错误信息
        'msg' => null,
        //错误代码或者返回的结果
        'errCode' => null,

    ];
    //阻止外部克隆书库工具类
    public $res = array(
        // 'sc' => 'err',
        // //只有2种状态 ok/error
        // 'data' => null,
        // //正确：返回数据
        // 'msg' => null,
        //错误：返回错误原因
    );
    private function __clone()
    {
    }

    //私有化构造方法初始化，禁止外部使用

    private function __construct($UserConfig)
    {
        // && $UserConfig['enabled']
        if ($UserConfig && $UserConfig['Debug']) {
            $this->errdb['table'] = $UserConfig['Error']['tableName'];
            $this->errdb['type'] = $UserConfig['Error']['type'];
            $this->errdb['time'] =  $UserConfig['Error']['time'];
            $this->errdb['fileName'] = $UserConfig['Error']['fileName'];
            $this->errdb['line'] = $UserConfig['Error']['line'];
            $this->errdb['msg'] = $UserConfig['Error']['msg'];
            $this->errdb['errCode'] = $UserConfig['Error']['errCode'];
        } else {
            return false;
        }

        // $this->res = array();
    }
    //内部产生静态对象
    public static function active($UserConfig)
    {
        if (!self::$obj instanceof self) {
            //如果不存在，创建保存
            self::$obj = new self($UserConfig);
        }
        return self::$obj;
    }

    /** */
    public function write($errData)
    {
        $data =   json_decode($errData, true);
        if (is_array($data)) {
            if (in_array($data['type'], [0, 1, 2])  && $data['fileName'] && $data['line'] && $data['msg'] && $data['errCode']) {
                return $data;
            } else {
                return [
                    'sc' => 'err',
                    'msg' => '参数错误',
                    'param' => [
                        'type' => 'int(0|1|2)',
                        'fileName' => '$_SERVER[SCRIPT_NAME]',
                        'line' => '__LINE__',
                        'msg' => '发生错的的相关信息',
                        'errCode' => 'json_encode(错误的返回数据信息)',
                        'uid' => ' guest | uid | keeper_uid | admin_uid ',
                    ],
                    'data' => $data,
                ];
            }
        } else {
            return false;
        }
    }
}
