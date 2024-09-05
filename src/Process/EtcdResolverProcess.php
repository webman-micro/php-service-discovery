<?php

namespace WebmanMicro\PhpServiceDiscovery\Process;

use support\Log;
use WebmanMicro\PhpServiceDiscovery\Etcd\Discovery;
use Workerman\Http\Response;
use WebmanMicro\PhpServiceDiscovery\Timer;
use Workerman\Worker;
use WebmanMicro\PhpServiceDiscovery\Traits\ErrorMsg;
use WebmanMicro\PhpServiceDiscovery\Cache\File;

class EtcdResolverProcess extends AbstractProcess
{
    use ErrorMsg;

    /** @var int 长轮询间隔 秒 */
    protected int $longPullingInterval;


    /** @var */
    protected $timer_id = null;

    /**
     * ConfigListenerProcess constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->longPullingInterval = config('plugin.webman-micro.php-service-discovery.app.long_pulling_interval', 2);
    }

    /**
     * @description 在进程退出时候，可能会报status 9，是正常现象
     * @param Worker $worker
     * @throws GuzzleException
     */
    public function onWorkerStart(Worker $worker)
    {
        $worker->count = 1;
        $this->timer_id = Timer::add(0.0,
            (float)$this->longPullingInterval,
            function () {
                $this->serviceDiscovery();
            });
    }

    /**
     * @inheritDoc
     */
    public function onWorkerStop(Worker $worker)
    {
        if (is_int($this->timer_id)) {
            Timer::del($this->timer_id);
            File::rmServiceUUID();
        }
    }

    /**
     * 维护服务发现
     * @return void
     */
    protected function serviceDiscovery()
    {
        foreach ($this->etcd_config["discovery_name"] as $discoveryName) {
            $res = $this->client->getKeysWithPrefix("discovery/{$discoveryName}");
            if (!empty($res['kvs'])) {
                // 把服务地址写入缓存
                $discoveryData = [];
                foreach ($res['kvs'] as $kv) {
                    $discoveryData[$kv['key']] = $kv['value'];
                }
                Discovery::instance()->refreshCache($discoveryName, $discoveryData);
            }
        }
    }
}
