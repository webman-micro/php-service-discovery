<?php
return [
    // 启动etcd进程
    'etcd.discovery' => [
        'handler'=> \WebmanMicro\PhpServiceDiscovery\Process\EtcdGoServer::class,
        'reloadable' => false,
        'count'  => 1,
    ]
];
