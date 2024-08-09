<?php

namespace WebmanMicro\PhpServiceDiscovery\Process;

use Ark\Filecache\FileCache;

class EtcdGoServer
{

    // go_etcd_client 进程id
    protected static string $phpEtcdClientPIDKey = "go_etcd_client_pid";

    /**
     * @var FileCache|null
     */
    protected static $cacheInstance = null;


    public function __construct()
    {
        self::exec();
    }

    /**
     * @return FileCache|null
     */
    public static function instance()
    {
        if (empty(self::$cacheInstance)) {
            $config = config('etcd', []);
            if (!isset($config['discovery'])) {
                throw new \RuntimeException("Etcd connection discovery not found");
            }

            // 文件缓存
            $cachePath = $config['discovery']['cache'] ?? __DIR__ . '/../log';
            self::$cacheInstance = new FileCache([
                'root' => $cachePath, // Cache root
                'ttl' => 0,
                'compress' => false,
                'serialize' => 'json',
            ]);
        }
        return self::$cacheInstance;
    }

    /**
     * 后台运行 go etcd ws 服务端
     */
    public static function exec()
    {
        // 判断当前ws进程是否存在
        $cmd = 'ps axu|grep "go_etcd_client"|grep -v "grep"|wc -l';
        $ret = shell_exec("$cmd");

        $ret = rtrim($ret, "\r\n");
        if ($ret === "0") {
            // 拉起 go_etcd_client 客户端并后台运行2
            exec("nohup " . __DIR__ . "/../../bin/go_etcd_client >/dev/null 2>&1 & echo $!", $output);

            // 记录 go_etcd_client 进程 id
            if (!empty($output[0])) {
                self::instance()->set(self::$phpEtcdClientPIDKey, (int)$output[0]);
            }
        }
    }

    /**
     * 杀死 go etcd ws 服务端
     */
    public static function kill()
    {
        // 关闭杀死 go_etcd_client
        $phpEtcdClientPID = self::instance()->get(self::$phpEtcdClientPIDKey);
        if ($phpEtcdClientPID > 0) {
            self::instance()->delete(self::$phpEtcdClientPIDKey);
            \posix_kill($phpEtcdClientPID, SIGKILL);
        }
    }
}
