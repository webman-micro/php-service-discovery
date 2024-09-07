# php-service-discovery
webman 基于etcd服务注册发现组件

[![Php Version](https://img.shields.io/badge/php-%3E=7.4-brightgreen.svg)](https://secure.php.net/)
[![Workerman Version](https://img.shields.io/badge/workerman-%3E=4.0.19-brightgreen.svg)](https://github.com/walkor/Workerman)
[![imi License](https://img.shields.io/badge/license-Apache%202.0-brightgreen.svg)](https://github.com/cgpipline/strack/blob/master/LICENSE)

PHP版本，基于Workerman的ETCD服务注册和发现。

- 支持 rpc 服务，采用 workerman text 协议，msgpack 压缩
- 支持 K8S service ip 自动获取，非K8S环境自动获取本地ip
- 支持 熔断器

# 安装使用

```
composer require php-service-discovery
```

# 配合使用组件

| 名称 | 说明 |
|---|---|
| webman-micro/php-json-rpc | rpc 组件|
| webman-micro/php-breaker | 熔断器组件|
