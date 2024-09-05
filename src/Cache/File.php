<?php

namespace WebmanMicro\PhpServiceDiscovery\Cache;

use Ark\Filecache\FileCache;

class File
{
    protected static $cacheInstance = null;

    protected static $keyName = "service_uuid";

    /**
     * @return FileCache|null
     */
    public static function connection()
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
     * 获取并记录uuid
     * @return void
     */
    public static function getServiceUUID()
    {
        $uuid = self::connection()->get(self::$keyName);

        if (empty($uuid)) {
            $uuid = \Webpatser\Uuid\Uuid::generate()->string;
            self::connection()->set(self::$keyName, (string)$uuid);
        }
return $uuid;
    }

    /**
     * 移除uuid
     * @return void
     */
    public static function rmServiceUUID()
    {
        self::connection()->delete(self::$keyName);
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::connection()->{$name}(... $arguments);
    }
}
