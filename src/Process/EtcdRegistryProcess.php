<?php

namespace WebmanMicro\PhpServiceDiscovery\Process;

use support\Log;
use WebmanMicro\PhpServiceDiscovery\Etcd\Registry;
use Workerman\Http\Response;
use WebmanMicro\PhpServiceDiscovery\Timer;
use Workerman\Worker;
use WebmanMicro\PhpServiceDiscovery\Traits\ErrorMsg;
use WebmanMicro\PhpServiceDiscovery\Cache\File;

class EtcdRegistryProcess extends AbstractProcess
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
        $this->register();

        $this->timer_id = Timer::add(0.0,
            (float)$this->longPullingInterval,
            function () {
                $this->keepAlive();
            });
    }

    /**
     * @inheritDoc
     */
    public function onWorkerStop(Worker $worker)
    {
        if (is_int($this->timer_id)) {
            Timer::del($this->timer_id);
            $this->revoke();
        }
    }

    /**
     * 获取服务注册名称
     * @return string
     */
    protected function getDiscoveryName()
    {
        $uuid = File::getServiceUUID();
        return "discovery/{$this->etcd_config['server_name']}/{$uuid}";
    }

    /**
     * 注册服务
     * @return void
     */
    protected function register()
    {
        // 1. Discovery name
        $discoveryName = $this->getDiscoveryName();
        $serverInfo = Registry::instance($this->etcd_config)->generateServerInfo();

        // 2. 生成租约
        try {
            $lease = $this->client->grant((int)$this->longPullingInterval * 5);
            $this->lease_id = (int)$lease["ID"];

            // 3. 写入服务
            $this->client->put((string)$discoveryName, (string)$serverInfo['server_host'], ['lease' => $this->lease_id]);
        } catch (\Exception $e) {
            // TODO
            return $this->setError(false, 'server notice：' . $e->getMessage());
        }
    }

    /**
     * 保活
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function keepAlive()
    {
        try {
            $res = $this->client->keepAlive($this->lease_id);
        } catch (\Exception $e) {
            $this->register();
        }
    }

    /**
     * @return void
     */
    protected function revoke()
    {
        $this->client->revoke($this->lease_id);
    }
}
