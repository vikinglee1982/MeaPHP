<?php
/*
 * @Author: vikinglee1982 87834084@qq.com
 * @Date: 2024-04-05 17:34:18
 * @LastEditors: vikinglee1982 87834084@qq.com
 * @LastEditTime: 2026-05-18 15:49:59
 * @FilePath: \工作台\Servers\huayun_server\MeaPHP\Core\Reply\Reply.php
 * @Description: tools工具类的返回类；这里定义了返回类的方法及返回内容；统一管理
 */



namespace MeaPHP\Core\Reply;

class Reply
{
    private static $obj = null;


    //阻止外部克隆书库工具类
    private function __clone() {}

    //私有化构造方法初始化，禁止外部使用
    private function __construct() {}
    //内部产生静态对象
    public static function active()
    {
        // echo "<hr>";
        // echo "建立了";
        // var_dump($dbkey);
        if (!self::$obj instanceof self) {
            //如果不存在，创建保存
            self::$obj = new self();
        }
        return self::$obj;
    }
    public static function To(string $state, string $msg = '', array $data = []): array
    {
        // 自动获取调用位置的行号、文件名和类名
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

        $fileInfo = isset($trace[0]['file']) ? " [File: " . basename($trace[0]['file']) . "]" : '';
        $lineInfo = isset($trace[0]['line']) ? " [Line: " . $trace[0]['line'] . "]" : '';
        if ($state != 'ok' && $state != 'err' && $state >= 2000 && $state <= 9000) {
            throw new \InvalidArgumentException("Invalid state Code: Reply 类返回状态码错误-'$state'$fileInfo$lineInfo");
        }

        //$data 为空时删除$res 中的 data
        // $msg = $msg ? $msg . $fileInfo . $lineInfo : null;
        $res = [
            'sc' => $state,
            'msg' => $msg ?: null,
            'data' => is_array($data) ? $data : ($data ?: null),
            'script' => $fileInfo . $lineInfo,
        ];

        return $res;
    }

    /**
     * SSE 流式响应 (Server-Sent Events)
     * 
     * @description 用于长耗时任务的实时进度推送,支持前端即时接收进度更新
     *              注意:此方法会直接输出 SSE 格式数据并立即刷新缓冲区,无需经过 Export::send()
     *              当 progress=100 时会自动关闭连接
     * @example 
     *   Reply::SSE(10, '正在解析URL...');                    // 发送进度消息
     *   Reply::SSE(50, '正在创建行程...', ['url' => $url]);  // 携带数据
     *   Reply::SSE(100, '创建完成!');                        // 自动关闭连接
     *   Reply::SSE(0, 'URL格式错误', [], 'error');           // 错误状态
     * 
     * @param int $progress 进度百分比 (0-100),达到100时自动关闭连接
     * @param string $message 提示消息
     * @param array $data 返回数据 (默认 [])
     * @param string $sc 状态码 (默认 'ok')
     * @param int $timeout 超时时间(秒),仅首次调用生效 (默认 120)
     * @return void
     */
    public static function SSE(
        int $progress,
        string $message = '',
        array $data = [],
        string $sc = 'ok',
        int $timeout = 120
    ): void {
        static $initialized = false;

        if (!$initialized) {
            // 设置脚本超时
            set_time_limit($timeout);

            // 彻底清空所有输出缓冲区
            while (@ob_get_level()) {
                @ob_end_clean();
            }

            // 尝试禁用 Apache 层压缩
            if (function_exists('apache_setenv')) {
                apache_setenv('no-gzip', '1');
                apache_setenv('dont-vary', '1');
            }

            // 关闭 PHP 层缓冲和压缩
            @ini_set('output_buffering', '0');
            @ini_set('zlib.output_compression', '0');
            @ini_set('implicit_flush', '1');

            // 设置 SSE 必需的 HTTP 头
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no'); // 禁用 Nginx/Aliyun SLB 缓冲
            header('Pragma: no-cache');
            header('Expires: 0');

            // ⭐⭐⭐ 关键：发送 8KB 填充注释（SSE 合法注释，浏览器忽略）
            echo ": " . str_repeat(" ", 8190) . "\n\n"; // 总共约 8192 字节
            flush();

            $initialized = true;
        }

        // 获取调用位置（可选，用于调试）
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $fileInfo = isset($trace[0]['file']) ? " [File: " . basename($trace[0]['file']) . "]" : '';
        $lineInfo = isset($trace[0]['line']) ? " [Line: " . $trace[0]['line'] . "]" : '';

        // 构建 SSE 数据
        $payload = json_encode([
            'recode' => $sc === 'ok' ? 2000 : 5000,
            'msg' => $message ?: null,
            'data' => $data ?: null,
            'progress' => $progress,
            'timestamp' => time(),
            'script' => $fileInfo . $lineInfo
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $output = "data: {$payload}\n\n";

        // ⭐⭐⭐ 关键：确保每次输出 >= 8KB，防止 Apache 合并小包
        if (strlen($output) < 8192) {
            $output .= str_repeat(" ", 8192 - strlen($output)) . "\n";
        }

        echo $output;
        flush(); // 强制刷新

        // 进度 100% 时关闭连接
        if ($progress === 100) {
            echo "event: close\ndata: done\n\n";
            flush();
            exit;
        }
    }

    /**
     * 提前响应：先返回数据给前端，后台继续执行耗时任务
     *
     * @description 立即将 $data 以标准 JSON 格式输出给浏览器（与 Export::send() 格式一致），
     *              前端 axios 立即收到响应后，PHP 进程不退出，继续执行 $fun 回调中的任务。
     *              任务执行完毕后自动 exit，调用方无需手动 exit。
     *
     * @param array    $data 必须符合 Reply::To() 的返回格式：
     *                       ['sc' => 'ok', 'msg' => '...', 'data' => [...]]
     * @param callable $fun  响应发送后立即执行的后续任务（如 AI 推理）
     *                       任务执行完毕后自动 exit，调用方无需关心
     * @return void
     */
    public static function Early(array $data, callable $fun): void
    {
        // 校验格式：必须有 sc 字段
        if (!isset($data['sc'])) {
            throw new \InvalidArgumentException(
                'Reply::Early() $data 必须包含 sc 字段，建议使用 Reply::To() 构造'
            );
        }

        // 组装完整响应 JSON（格式与 Export::send() 一致）
        $recode = ($data['sc'] === 'ok') ? 2000 : 5000;
        $json = json_encode([
            'recode' => $recode,
            'msg'    => $data['msg'] ?? '',
            'data'   => $data['data'] ?? [],
            'ms'     => date('Y-m-d h:i:s', time()),
            'trs'    => round(microtime(true) * 1000),
            'api'    => '',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // --- 提前输出响应 ---
        ignore_user_abort(true);
        set_time_limit(120);
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
            @apache_setenv('dont-vary', '1');
        }
        @ini_set('zlib.output_compression', '0');
        @ini_set('output_buffering', '0');
        @ini_set('implicit_flush', '1');
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Length: ' . strlen($json));
        echo $json;
        flush();

        // --- 执行后续任务 ---
        try {
            $fun();
        } catch (\Throwable $e) {
            error_log('[Reply::Early] 后台任务执行失败: ' . $e->getMessage()
                . ' in ' . $e->getFile() . ':' . $e->getLine());
        }

        // 统一退出，防止调度器重复输出
        exit;
    }

}
