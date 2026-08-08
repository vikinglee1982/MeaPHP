<?php

namespace MeaPHP\Core\Tools;

use MeaPHP\Core\Reply\Reply;
use MeaPHP\Core\Utils\Path;

class Directory
{
    /**
     * @var self|null 单例对象实例
     */
    private static $obj = null;

    private function __clone() {}
    private function __construct() {}

    public static function active(): self
    {
        if (!self::$obj instanceof self) {
            self::$obj = new self();
        }
        return self::$obj;
    }
    /**
     * 判断目录是否存在
     */
    public function exists(string $path): array
    {
        $fullPath = Path::toLocalPath($path);
        if (!Path::isUnderRoot($fullPath)) {
            return Reply::To('err', '路径不安全');
        }

        $exists = is_dir($fullPath);
        if ($exists) {
            return Reply::To('ok', '目录存在', [
                'exists' => $exists,
                'path' => $fullPath
            ]);
        } else {
            return Reply::To('err', '目录不存在', [
                'exists' => $exists,
                'path' => $fullPath
            ]);
        }
    }

    /**
     * 创建目录（递归）
     */
    public function create(string $path, int $mode = 0755): array
    {
        $fullPath = Path::toLocalPath($path);
        if (!Path::isUnderRoot($fullPath)) {
            return Reply::To('err', '路径不安全');
        }

        if (is_dir($fullPath)) {
            return Reply::To('ok', '目录已存在', ['path' => $fullPath]);
        }

        if (mkdir($fullPath, $mode, true)) {
            return Reply::To('ok', '目录创建成功', ['path' => $fullPath]);
        }

        return Reply::To('err', '目录创建失败', ['path' => $fullPath]);
    }

    /**
     * 删除目录
     *
     * @param string $path 目录路径（相对于根目录）
     * @param bool $includeSelf 是否删除目录自身（true=删除整个目录；false=仅清空内容）
     */
    public function delete(string $path, bool $includeSelf = true): array
    {
        $fullPath = Path::toLocalPath($path);
        if (!Path::isUnderRoot($fullPath)) {
            return Reply::To('err', '路径不安全');
        }

        if (!is_dir($fullPath)) {
            return Reply::To('err', '目录不存在', ['path' => $fullPath]);
        }

        $success = $this->deleteRecursive($fullPath, $includeSelf);
        $action = $includeSelf ? '删除目录' : '清空目录';
        $msg = $success ? "$action 成功" : "$action 失败";

        return $success
            ? Reply::To('ok', $msg, ['path' => $fullPath])
            : Reply::To('err', $msg, ['path' => $fullPath]);
    }

    /**
     * 列出目录下所有文件和子目录（非递归）
     */
    public function listContents(string $path): array
    {
        $fullPath = Path::toLocalPath($path);
        if (!Path::isUnderRoot($fullPath)) {
            return Reply::To('err', '路径不安全');
        }

        if (!is_dir($fullPath)) {
            return Reply::To('err', '目录不存在');
        }

        $items = array_values(array_diff(scandir($fullPath), ['.', '..']));
        return Reply::To('ok', '获取成功', $items);
    }

    /**
     * 递归删除目录内容
     *
     * @param string $dir 本地绝对路径
     * @param bool $deleteSelf 是否删除目录自身
     * @return bool 操作是否成功
     */
    private function deleteRecursive(string $dir, bool $deleteSelf = true): bool
    {
        if (!is_dir($dir)) {
            // 如果路径不是目录，则只有在“不需要删自己”时才算成功
            return !$deleteSelf;
        }

        $dir = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR;

        foreach (glob($dir . '*') as $entry) {
            if (is_dir($entry)) {
                if (!$this->deleteRecursive($entry, true)) {
                    return false;
                }
            } else {
                if (!unlink($entry)) {
                    return false;
                }
            }
        }

        // 最后决定是否删除目录自身
        return $deleteSelf ? rmdir($dir) : true;
    }
}
