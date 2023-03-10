<?php

namespace  MeaPHP\Core\Tools;

/**
 * 文件的上传保存，移动，复制等关于文件的操作都整理到这一个

 */

class File
{

    private static $obj = null;

    public $res = array(
        // 'status' => 'error',
        // //只有2种状态 ok/error
        // 'data' => null,
        // //正确：返回数据
        // 'msg' => null,
        //错误：返回错误原因
    );
    private $tempRes = array();

    //阻止外部克隆书库工具类

    private function __clone()
    {
    }

    //私有化构造方法初始化，禁止外部使用

    private function __construct()
    {
        $this->res = array();
    }
    //内部产生静态对象
    public static function active()
    {
        // var_dump( $dbkey );
        if (!self::$obj instanceof self) {
            //如果不存在，创建保存
            self::$obj = new self();
        }
        return self::$obj;
    }
    //单文件移动

    public function MoveMonofile($oldName = '', $newName = '', $mkdir = false)
    {
        // $oldName = '' 旧文件的完全路径
        // $newName = '' 移动的新位置；
        // $mkdir = false 新位置文件夹不存在的情况下是否新建文件夹

        //这里需要做地址拼接
        if ($oldName) {
            if (substr($oldName, 0, 1) !== '/') {
                $oldName = '/' . $oldName;
            }
            $oldName = iconv('utf-8', 'gbk', $_SERVER['DOCUMENT_ROOT']  . $oldName);
        }

        if (!$oldName) {
            $this->res['status'] = 'error';
            $this->res['msg'] = __CLASS__ . '->' . __LINE__ . '缺少oldName';
        } else if (!$newName) {
            $this->res['status'] = 'error';
            $this->res['msg'] = __CLASS__ . '->' . __LINE__ . '缺少newName';
        } else if (!is_file($oldName)) {
            $this->res['status'] = 'error';
            $this->res['msg'] = __CLASS__ . '->' . __LINE__ . ':未找到oldname的文件';
            // $this->res[ 'oldName' ] = $oldName;
        } else {
            //如果传入的新文件的路径不带‘/’, 添加一个‘/’
            if (substr($newName, 0, 1) !== '/') {
                $newName = '/' . $newName;
            }
            //合并当前服务器完成的文件路径
            $newName = iconv('utf-8', 'gbk', $_SERVER['DOCUMENT_ROOT']  . $newName);

            //获取当前文件所在的目录
            $dirname = dirname($newName);
            //如果用户打开了：无当前目录自动创建目录;
            // 并且：当前目录不存在;
            // 就创建该目录;
            if ($mkdir && !file_exists($dirname)) {
                mkdir(iconv('UTF-8', 'GBK', $dirname), 0777, true);
            }
            //如果当前目录不存在：报错;
            //如果存在开始移动文件
            if (file_exists($dirname)) {
                $r = rename($oldName, $newName);
                if ($r) {
                    $path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $newName);
                    $this->res['status'] = 'ok';
                    $this->res['data'] = $path;
                } else {
                    $this->res['status'] = 'error';
                    $this->res['msg'] = __CLASS__ . '->' . __LINE__ . ':' . '移动文件失败';
                }
            } else {
                $this->res['status'] = 'error';
                $this->res['msg'] = __CLASS__ . '->' . __LINE__ . ':' . '新文件所在的目录不存在';
            }
        }
        return $this->res;
    }

    //数组式多文件移动

    public function MoveMultifile($arr, $mkdir = false)
    {
        if (!is_array($arr)) {
            $this->res['status'] = 'error';
            $this->res['msg'] = __CLASS__ . '->' . __LINE__ . '入参格式必须是array';
        } else {
            $pass = true;
            foreach ($arr as $k => $v) {
                if (!$v['oldName']) {
                    $pass = false;
                    break;
                }
                if (!$v['newName']) {
                    $pass = false;
                    break;
                }
            }
            if ($pass) {
                $this->recursion($arr, $mkdir);
            } else {
                $this->res['status'] = 'error';
                $this->res['msg'] = __CLASS__ . '->' . __LINE__ . ':' . '入参的数组格式： [
                    0:{
                        oldName:当前文件包含文件名的完整目录,
                        newName:需要移动到的完整目录，且包含文件名,
                    }
                    1:{
                        oldName:当前文件包含文件名的完整目录,
                        newName:需要移动到的完整目录，且包含文件名,
                    }
                ]';
            }
        }
        return $this->res;
    }
    //递归循环调用储存

    private function recursion($arr, $mkdir, $i = 0)
    {
        $res = $this->MoveMonofile($arr[$i]['oldName'], $arr[$i]['newName'], $mkdir);
        if ($res['status'] == 'ok') {
            //临时储存已经储存的图片路径
            array_push($this->tempRes, $this->res['data']);
            $i++;
            if ($arr[$i]) {
                $this->recursion($arr, $mkdir, $i);
            } else {
                $this->res['status'] = 'ok';
                $this->res['data'] = $this->tempRes;
                return $this->$res;
            }
        } else {
            return $this->res;
        }
    }
    //单文件复制到新的路径
    public function CopyMonofile($oldFilePath, $folder)
    {

        if (!$oldFilePath) {
            $this->res['status'] = 'error';
            $this->res['msg'] = __CLASS__ . '->' . __LINE__ . '缺少需要拷贝的文件路径';
            return $this->res;
        }
        if (!$folder) {
            $this->res['status'] = 'error';
            $this->res['msg'] = __CLASS__ . '->' . __LINE__ . '缺少存放路径';
            return $this->res;
        }

        // //这里需要做地址拼接

        if (substr($oldFilePath, 0, 1) !== '/') {
            $oldFilePath = '/' . $oldFilePath;
        }
        $oldFilePath = iconv('utf-8', 'gbk', $_SERVER['DOCUMENT_ROOT']  . $oldFilePath);


        if (substr($folder, 0, 1) !== '/') {
            $folder = '/' . $folder;
        }
        $folder = iconv('utf-8', 'gbk', $_SERVER['DOCUMENT_ROOT']  . $folder);


        if (!is_file($oldFilePath)) {
            $this->res['status'] = 'error';
            $this->res['msg'] = __CLASS__ . '->' . __LINE__ . '没有发现需要拷贝的文件';
            return $this->res;
        } else {
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }
            if (!is_dir($folder)) {
                $this->res['status'] = 'error';
                $this->res['msg'] = __CLASS__ . '->' . __LINE__ . '创建存放新文件目录失败';
                return $this->res;
            }
        }

        // # code...
    }
}
