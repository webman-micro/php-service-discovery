<?php

namespace WebmanMicro\PhpServiceDiscovery\Etcd;

use Workbunny\WebmanSharedCache\Cache;
use WebmanMicro\PhpServiceDiscovery\LoadBalancer\LoadBalancerInterface;
use WebmanMicro\PhpServiceDiscovery\LoadBalancer\RoundRobinBalancer;

class Discovery
{
    /**
     * @var object 对象实例
     */
    protected static $instance = null;


    // cache key
    protected static $cacheKey = '';

    /**
     * 负载均衡器
     * 默认为 RoundRobinBalancer
     * @var LoadBalancerInterface
     */
    public $loadBalancer;


    // 注册参数
    protected $serverInfo = [
        'method' => 'discovery',
        'etcd_host' => '',
        'param' => ''
    ];

    /**
     * Discovery constructor.
     */
    public function __construct()
    {
        if (empty(self::$cacheKey)) {
            self::$cacheKey = "etcd_discovery" . $this->getServiceUUID();
            $this->loadBalancer = $this->getDefaultLoadBalancer();
        }
    }

    protected function getServiceUUID()
    {
        $uuid = Cache::Get('service_uuid');

        if (empty($uuid)) {
            $uuid = \Webpatser\Uuid\Uuid::generate()->string;
            Cache::Set('service_uuid', (string)$uuid);
        }
        return $uuid;
    }

    /**
     * Default LoadBalancer
     * @return RoundRobinBalancer
     */
    protected function getDefaultLoadBalancer()
    {
        return new RoundRobinBalancer();
    }

    /**
     * 初始化
     * @return object|static|null
     */
    public static function instance()
    {
        if (!empty(self::$instance)) {
            return self::$instance;
        }

        self::$instance = new static();
        return self::$instance;
    }

    /**
     * 把服务地址写入缓存
     * @param $name
     * @param array $discoveryData
     */
    public function refreshCache($name, $discoveryData = [])
    {
        $cache = Cache::Get(self::$cacheKey);
        if (empty($cache) && !isset($cache)) {
            $cacheArray = [];
        } else {
            $cacheArray = json_decode($cache, true);
        }

        $cacheArray[$name] = $discoveryData;
        Cache::Set(self::$cacheKey, json_encode($cacheArray), ['EX' => 5]);
    }

    /**
     * 通过服务名称获取服务配置
     * @param $name
     * @return string
     */
    public function getServerConfigByName($name)
    {
        $cache = Cache::Get(self::$cacheKey);

        //  负载均衡取节点
        if (!empty($cache)) {
            $cacheArray = json_decode($cache, true);
            if (array_key_exists($name, $cacheArray)) {
                return $this->loadBalancer->invoke($cacheArray[$name]);
            }
        }
        return '';
    }
}
