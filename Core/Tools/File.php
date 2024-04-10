<?php

namespace  MeaPHP\Core\Tools;

use MeaPHP\Core\Reply\Reply;

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


    /**
     * @description: 单文件移动
     * @return {*}
     */

    public function MoveMonofile(string $oldName = null, string $newName = null, bool $mkdir = false)
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

    //
    /**
     * @description: 数组式多文件移动
     * @return {*}
     */

    public function MoveMultifile(array $arr = [], $mkdir = false)
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
                $this->moveRecursion($arr, $mkdir);
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

    /**
     * @description: 私有方法：//递归循环调用储存
     * @return {*}
     */

    private function moveRecursion($arr, $mkdir, $i = 0)
    {
        $res = $this->MoveMonofile($arr[$i]['oldName'], $arr[$i]['newName'], $mkdir);
        if ($res['status'] == 'ok') {
            //临时储存已经储存的图片路径
            array_push($this->tempRes, $this->res['data']);
            $i++;
            if ($arr[$i]) {
                $this->moveRecursion($arr, $mkdir, $i);
            } else {
                $this->res['status'] = 'ok';
                $this->res['data'] = $this->tempRes;
                return $this->$res;
            }
        } else {
            return $this->res;
        }
    }
    /**
     * @description: //单文件复制到新的路径
     * @return {*}
     */

    public function CopyMonofile(string $oldFilePath, string $folder, bool $mkdir = false)
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
        } else {

            //如果当前文件夹不存在，并且用户允许新创建
            if (!file_exists(dirname($folder)) && $mkdir) {
                mkdir(iconv('UTF-8', 'GBK', dirname($folder)), 0777, true);
            }

            //如果存在当前文件夹
            if (file_exists(dirname($folder))) {
                //如果没有这个文件,就保存这个文件
                if (!is_dir($folder)) {
                    // mkdir($folder, 0777, true);
                    copy($oldFilePath, $folder);
                }
                // //这个文件存在了,就返回成功
                if (file_exists($folder)) {
                    $path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $folder);
                    $this->res['status'] = 'ok';
                    $this->res['data'] = $path;
                    return $this->res;
                } else {
                    $this->res['status'] = 'error';
                    $this->res['msg'] = __CLASS__ . '->' . __LINE__ . '复制保存文件失败';
                    $this->res['msg1'] = is_dir($folder);
                    $this->res['msg2'] = $folder;
                }
            } else {
                //不存在，就是创建失败
                $this->res['status'] = 'error';
                $this->res['msg'] = __CLASS__ . '->' . __LINE__ . '不存在需要拷贝的文件夹，或者入参创建不存在的文件夹';
            }
        }
        return $this->res;
    }


    /**
     * @description: 数组式拷贝文件
     * @param {array} $arr
     * @param {*} $mkdir
     * @return {*}
     */
    public function copyMultifile(array $arr = [], $mkdir = false): array
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
                $this->copyRecursion($arr, $mkdir);
            } else {
                $this->res['status'] = 'error';
                $this->res['msg'] = __CLASS__ . '->' . __LINE__ . ':' . '入参的数组格式： [
                    0:{
                        oldName:当前文件包含文件名的完整目录,
                        newName:需要拷贝到的完整目录，且包含文件名,
                    }
                    1:{
                        oldName:当前文件包含文件名的完整目录,
                        newName:需要拷贝到的完整目录，且包含文件名,
                    }
                ]';
            }
        }
        return $this->res;
    }

    /**
     * @description: 递归拷贝文件
     * @param {*} $arr
     * @param {*} $mkdir
     * @param {*} $i
     * @return {*}
     */
    private function copyRecursion($arr, $mkdir, $i = 0)
    {
        $res = $this->CopyMonofile($arr[$i]['oldName'], $arr[$i]['newName'], $mkdir);
        if ($res['status'] == 'ok') {
            //临时储存已经储存的图片路径
            array_push($this->tempRes, $this->res['data']);

            $i++;

            if ($arr[$i]) {
                $this->copyRecursion($arr, $mkdir, $i);
            } else {
                $this->res['status'] = 'ok';
                $this->res['data'] = $this->tempRes;
                return $this->$res;
            }
        } else {
            return $this->res;
        }
    }

    /**
     * @description: 保存文件，单张图片
     * @return {*}
     */

    public function saveImage($file = null, $folderName = null, $fileName = null)

    {
        // return $file;
        // return  'jianlile ';
        //将路径全部转为小写
        // $folderName = strtolower($folderName);
        // //判断入参的文件类型，必须时图片格式
        $fileType = $file['type'];
        if (!$file) {
            $this->res['status'] = "error";
            $this->res['msg'] = '请缺少入参image文件';
            // return "error:请缺少入参image文件";
        } elseif ($fileType != 'image/jpeg' && $fileType != 'image/png' && $fileType != 'image/webp' && $fileType != 'image/gif') {

            $this->res['status'] = "error";
            $this->res['msg'] = '文件类型支持[gif/jpg/jpge/png//webp]';
            $this->res['type'] = $fileType;
            $this->res['file'] = $file;
            // return "error:文件类型支持[gif/jpg/jpge/png//webp]";
            //image/jpeg;image/png;image/webp;image/gif
            // return $file['type'];
        } elseif (!$folderName) {
            $this->res['status'] = "error";
            $this->res['msg'] = '缺少文件夹目录（从项目根路径下开始的目录）';
            // return "error:缺少文件夹目录（项目根路径下的目录）";
        } elseif (strpos($folderName, 'undefined') || strpos($folderName, 'null')) {
            $this->res['status'] = "error";
            $this->res['msg'] = '文件夹目录中不能存在undefined或者null等字段';
            // return "error:缺少文件夹目录（项目根路径下的目录）";
        } else {


            if (!$fileName) {
                //如果用户没有传进文件名，就生成一个，不重复的文件名
                $fileName = $this->createFileName($file);
            }

            //合成需要存放文件的路径
            // $userDir = iconv('utf-8', 'gbk', $this->basicsPath . '/' . $folderName . '/' . $fileType);
            if (substr($folderName, 0, 1) !== '/') {
                $folderName = '/' . $folderName;
            }

            $userDir = iconv('utf-8', 'gbk', $_SERVER['DOCUMENT_ROOT']  . $folderName);


            if (!is_dir($userDir)) {

                mkdir(iconv("UTF-8", "GBK", $userDir), 0777, true);
            }

            //如果路径最后一位写了 / ,拼接时就不加入 / ，否则加入
            if (substr($userDir, -1) == '/') {
                $src = $userDir . $fileName;
            } else {
                $src = $userDir . '/' . $fileName;
            }

            //开始储存



            if (file_exists($src)) {
                $this->res['status'] = "error";
                $this->res['msg'] = '文件名重复';
                // $this->res['msg'] = $src;
                // return "error:已经有这个文件了";
            } else {
                if (move_uploaded_file($file['tmp_name'], $src)) {
                    //储存完成合成路径返回
                    //
                    // $url = str_replace($_SERVER['DOCUMENT_ROOT'], $_SERVER['HTTP_HOST'], $src);
                    $url = str_replace($_SERVER['DOCUMENT_ROOT'], '', $src);



                    $this->res['status'] = "ok";
                    $this->res['data'] =  $url;
                    // return $url;
                } else {
                    //储存失败
                    $this->res['status'] = "error";
                    $this->res['msg'] = '储存失败：' . $src;
                    // $data['src'] = $src;
                    // return 3000;
                    // return $src;
                }
            }
        }
        return $this->res;
    }

    /**
     * @description: 私有方法：创建文件名
     * @return {*}
     */
    private function createFileName($file)
    {

        //获取文件后缀名
        $extension = substr(strrchr($file['name'], '.'), 1);
        // md5(uniqid(md5(microtime(true)), true));
        // return md5($this->folderName . md5(uniqid(md5(microtime(true)), true)) . $fileType) . '.' . $extension;
        return md5(md5(uniqid(md5(microtime(true)), true)) . $file['type']) . '.' . $extension;
    }


    /**
     * @description: 私有方法：递归循环调用储存
     * @param {*} $arr
     * @param {*} $i
     * @return {*}
     */
    // private function saveRecursion($arr, $i = 0)
    // {
    //     $res = $this->image($arr[$i]['resource'], $arr[$i]['path']);
    //     if ($res['status'] == 'ok') {
    //         //临时储存已经储存的图片路径
    //         array_push($this->tempRes, $this->res['data']);
    //         $i++;
    //         if ($arr[$i]) {
    //             $this->recursion($arr, $i);
    //         } else {
    //             $this->res['status'] = 'ok';
    //             $this->res['data'] = $this->tempRes;
    //             return $this->$res;
    //         }
    //     } else {
    //         return $this->res;
    //     }
    // }

    /**
     * @description:删除单、多文件
     * @param {array|pathStr} $arr
     * @return {*}
    
     */
    public function delFile($pathData): array
    {
        if (is_array($pathData)) {
            // 处理数组的情况
            $arr = $pathData;
            if (count($arr) < 1) {
                return  Reply::To('err', '参数1:$arr为空');
                // $this->res['status'] = 'error';
                // $this->res['msg'] = '参数1:$arr为空';
            } else {
                $len = strlen($_SERVER['DOCUMENT_ROOT']);
                $arr =   array_values($arr);
                foreach ($arr as $k => $v) {
                    if (substr($v, 0, $len) != $_SERVER['DOCUMENT_ROOT']) {
                        $arr[$k] = $_SERVER['DOCUMENT_ROOT'] . $v;
                    }
                }
                $del = true;
                foreach ($arr as $k => $v) {
                    if (!file_exists($v)) {

                        return  Reply::To('err', '第' . $k . '项（' . $v . '),文件不存在');
                        // $this->res['status'] = 'error';
                        // $this->res['msg'] = '第' . $k . '项（' . $v . '),文件不存在';
                        $del = false;
                        break;
                    }
                }
                if ($del) {
                    foreach ($arr as $k => $v) {
                        if (unlink($v)) {
                            if ((count($arr) - 1) == $k) {
                                // $this->res['status'] = 'ok';
                                // $this->res['msg'] = $k + 1;

                                return  Reply::To('ok', $k + 1);
                            }
                        } else {
                            return  Reply::To('err', '第' . $k . '项删除失败');
                            // $this->res['status'] = 'error';
                            // $this->res['msg'] = '第' . $k . '项删除失败';
                        }
                    }
                }
            }
        } elseif (is_string($pathData)) {

            $path = $_SERVER['DOCUMENT_ROOT'] . $pathData;
            if (!is_file($path)) {
                return  Reply::To('err', '未找到当前文件');
                // $this->res['status'] = 'err';
                // $this->res['msg'] = '';
            }
            // 处理字符串的情况
            $res = unlink($path);
            if ($res) {
                return  Reply::To('ok', '删除单文件成功');
                // $this->res['status'] = 'ok';
                // $this->res['msg'] = '删除单文件成功';
            } else {
                return  Reply::To('err', '删除单文件失败');
                // $this->res['status'] = 'error';
                // $this->res['msg'] = '';
                // $this->res['msg1'] = $res;
                // $this->res['path'] = $path;
            }
        } else {
            return  Reply::To('err', '参数格式错误：string|array');
            // $this->res['status'] = 'error';
            // $this->res['msg'] = '参数格式错误：string|array';
            // throw new InvalidArgumentException('Invalid argument type, expected an array or string');
        }




        // return $this->res;
    }
}
