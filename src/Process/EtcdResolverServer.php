<?php

namespace WebmanMicro\PhpServiceDiscovery\Process;

use WebmanMicro\PhpServiceDiscovery\Etcd\Client as EtcdClient;
use WebmanMicro\PhpServiceDiscovery\Etcd\Discovery;
use Workerman\Timer;
use support\Log;

class EtcdResolverServer
{

    // 服务Etcd Host
    public $etcdConfig = [];

    // etcd客户端
    protected $etcdClient = null;

    // 定时ID
    protected $timerId = 0;

    /**
     * Etcd constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $config = config('etcd', []);
        if (!isset($config['discovery'])) {
            throw new \RuntimeException("Etcd connection discovery not found");
        }
        $this->etcdConfig = $config['discovery'];
        $this->etcdClient = new EtcdClient($config['discovery']['etcd_host']);

        $this->serviceDiscovery();
    }

    /**
     * 维护服务发现
     * @return void
     */
    protected function serviceDiscovery()
    {
        // 每隔1秒维护服务节点状态
        Timer::add(1, function () {
            foreach ($this->etcdConfig["discovery_name"] as $discoveryName) {
                $res = $this->etcdClient->getKeysWithPrefix("discovery/{$this->etcdConfig['server_name']}");

                var_dump($res);
            }
        });
    }

    /**
     * 处理消息
     * @param AsyncTcpConnection $connection
     * @param $data
     */
    protected function wsOnMessage($data)
    {
        $dataArr = json_decode($data, true);
        if (is_array($dataArr)) {
            if ($dataArr['code'] >= 0) {
                // 正常返回
                switch ($dataArr['method']) {
                    case 'register':
                        // 注册成功开启服务发现定时任务
                        $this->serviceDiscovery();
                        break;
                    case 'discovery':
                        // 服务发现返回数据写入Cache
                        Discovery::instance()->refreshCache($dataArr['data']['server_name'], $dataArr['data']);
                        break;
                }
            } else {
                // 数据异常写入日志
                Log::channel()->error($dataArr['msg']);
            }

        } else {
            // 数据异常写入日志
            Log::channel()->error($data);
        }
    }
}
