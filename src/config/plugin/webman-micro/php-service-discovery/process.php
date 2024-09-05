<?php
return [
    // 启动etcd进程
    'etcd.registry' => [
        'handler'=> \WebmanMicro\PhpServiceDiscovery\Process\EtcdRegistryProcess::class,
        'reloadable' => false,
        'count'  => 1,
    ],
    'etcd.discovery' => [
        'handler'=> \WebmanMicro\PhpServiceDiscovery\Process\EtcdResolverProcess::class,
        'count'  => 1,
    ]
];
