<?php

namespace MeaPHP\Core\Utils;


/**
 * 路径处理工具类（无状态、纯静态）
 * 负责：标准化、安全校验、拼接、URL 转换等
 */
class Path
{
    /**
     * 获取服务器文档根目录（DOCUMENT_ROOT），带兜底
     */
    public static function getDocumentRoot(): string
    {
        return $_SERVER['DOCUMENT_ROOT'] ?? rtrim($_SERVER['PWD'] ?? '/var/www/html', '/');
    }

    /**
     * 确保路径以 / 开头（统一为绝对路径风格）
     */
    public static function ensureLeadingSlash(string $path): string
    {
        return ltrim($path, '/');
    }

    /**
     * 将用户输入路径解析为绝对本地路径
     * 示例：
     *   "/upload/test.jpg" → "/var/www/html/upload/test.jpg"
     *   "upload/test.jpg"  → "/var/www/html/upload/test.jpg"
     */
    public static function toLocalPath(string $inputPath): string
    {
        $clean = '/' . self::ensureLeadingSlash($inputPath);
        return self::getDocumentRoot() . $clean;
    }

    /**
     * 标准化路径（移除 . / .. / 多余斜杠）
     * 防止路径穿越攻击
     */
    public static function normalize(string $path): string
    {
        // 统一为 Unix 风格
        $path = str_replace('\\', '/', $path);
        // 移除开头的 /
        $path = ltrim($path, '/');
        // 拆分并处理 ..
        $parts = [];
        foreach (explode('/', $path) as $part) {
            if ($part === '..') {
                array_pop($parts);
            } elseif ($part !== '.' && $part !== '') {
                $parts[] = $part;
            }
        }
        return implode('/', $parts);
    }

    /**
     * 判断路径是否在指定根目录内（防路径穿越核心）
     */
    public static function isUnderRoot(string $path, ?string $root = null): bool
    {
        $root = $root ?? self::getDocumentRoot();

        // 获取真实路径（若文件不存在，realpath 返回 false）
        $realPath = realpath($path);
        $realRoot = realpath($root);

        if ($realPath === false || $realRoot === false) {
            // 若路径不存在，先标准化再比对前缀（防御性处理）
            $absPath = self::toAbsolutePath($path, $root);
            $absRoot = rtrim(str_replace('\\', '/', $root), '/');
            return strpos($absPath, $absRoot) === 0;
        }

        // 统一为 / 分隔符
        $realPath = str_replace('\\', '/', $realPath);
        $realRoot = str_replace('\\', '/', $realRoot);

        return strpos($realPath, $realRoot) === 0;
    }

    /**
     * 辅助：将任意路径转为基于 root 的绝对路径（不依赖文件存在）
     */
    private static function toAbsolutePath(string $path, string $root): string
    {
        $root = rtrim(str_replace('\\', '/', $root), '/');
        $norm = self::normalize($path);
        return $root . ($norm ? '/' . $norm : '');
    }

    /**
     * 将本地绝对路径转为 URL（不含协议和域名）
     * 示例：/var/www/html/upload/test.jpg → /upload/test.jpg
     */
    public static function toUrlPath(string $localPath): string
    {
        $docRoot = str_replace('\\', '/', self::getDocumentRoot());
        $localPath = str_replace('\\', '/', $localPath);
        if (strpos($localPath, $docRoot) === 0) {
            return substr($localPath, strlen($docRoot)) ?: '/';
        }
        throw new \InvalidArgumentException('路径不在 Web 根目录下');
    }

    /**
     * 安全地拼接路径（自动标准化）
     */
    public static function join(...$parts): string
    {
        $filtered = array_filter($parts, fn($p) => $p !== null && $p !== '');
        return implode('/', array_map([self::class, 'normalize'], $filtered));
    }
}
