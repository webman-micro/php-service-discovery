<?php

namespace WebmanMicro\PhpServiceDiscovery\Etcd;

use WebmanMicro\PhpServiceDiscovery\Cache\File;

class Registry
{
    /**
     * @var object 对象实例
     */
    protected static $instance = null;

    // 服务Etcd配置
    public static $serverConfig = [];

    // 注册参数
    protected $serverInfo = [];

    /**
     * Registry constructor.
     * @param array $etcdConfig
     */
    public function __construct(array $etcdConfig = [])
    {
        self::$serverConfig = $etcdConfig;
    }

    /**
     * 初始化
     * @param array $etcdConfig
     * @return object|static|null
     */
    public static function instance(array $etcdConfig = [])
    {
        if (!empty(self::$instance)) {
            return self::$instance;
        }

        self::$instance = new static($etcdConfig);
        return self::$instance;
    }

    /**
     * 获取服务注册参数配置
     * @return array|mixed
     */
    public function generateServerInfo()
    {
        if (!empty($this->serverInfo)) {
            return $this->serverInfo;
        }

        // 获取指定服务名称的ip，K8S里面使用
        $ip = gethostbyname(self::$serverConfig['server_name']);
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // 如果不是合法的IPV4 IP，则获取本机ip
            $ip = trim(shell_exec('hostname -i'));
        }

        // 获取当前服务ip
        $this->serverInfo['server_host'] = $ip . ":" . (self::$serverConfig['server_port'] ?? '');
        $this->serverInfo['server_name'] = self::$serverConfig['server_name'] ?? '';
        $this->serverInfo['server_id'] = File::getServiceUUID() ?? '';

        return $this->serverInfo;
    }
}
