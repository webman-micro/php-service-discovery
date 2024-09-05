<?php

namespace WebmanMicro\PhpServiceDiscovery\LoadBalancer;

/**
 * Interface LoadBalancerInterface
 */
interface LoadBalancerInterface
{

    /**
     * Invoke
     * @param array $services
     * @return array
     */
    public function invoke(array $services);

}
