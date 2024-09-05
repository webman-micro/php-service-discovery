<?php

declare(strict_types=1);

namespace WebmanMicro\PhpServiceDiscovery\Process;

use WebmanMicro\PhpServiceDiscovery\Etcd\Client as EtcdClient;
use WebmanMicro\PhpServiceDiscovery\Traits\Logger;
use Workerman\Worker;

abstract class AbstractProcess
{
    use Logger;

    /** @var EtcdClient */
    protected EtcdClient $client;

    /** @var array 服务列表配置 */
    protected array $etcd_config = [];

    /** @var int leaseId */
    protected      int $lease_id = 0;

    public function __construct()
    {
        $config = config('etcd', []);
        if (!isset($config['discovery'])) {
            throw new \RuntimeException("Etcd connection discovery not found");
        }
        $this->etcd_config = $config['discovery'];
        $this->client = new EtcdClient($config['discovery']['etcd_host']);
    }

    /**
     * @param Worker $worker
     * @return mixed
     */
    abstract public function onWorkerStart(Worker $worker);

    /**
     * @param Worker $worker
     * @return mixed
     */
    abstract public function onWorkerStop(Worker $worker);
}
