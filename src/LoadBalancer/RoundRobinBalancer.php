<?php

namespace WebmanMicro\PhpServiceDiscovery\LoadBalancer;

/**
 * Class RoundRobinBalancer
 */
class RoundRobinBalancer implements LoadBalancerInterface
{

    /**
     * @var string
     */
    protected $lastID;

    /**
     * Invoke
     * @param array $services
     * @return array
     */
    public function invoke(array $services)
    {
        $keys = array_keys($services);
        $lastID = $this->lastID;
        if (!$lastID) {
            $firstID = array_shift($keys);
            $this->lastID = $firstID;
            return $services[$firstID];
        }
        if ($lastID && !isset($services[$lastID])) {
            $randomID = array_rand($services);
            $this->lastID = $randomID;
            return $services[$randomID];
        }
        $nextID = null;
        foreach ($keys as $k => $id) {
            if ($id != $lastID) {
                continue;
            }
            if (isset($keys[$k + 1])) {
                $nextID = $keys[$k + 1];
                break;
            }
            $nextID = array_shift($keys);
        }
        $this->lastID = $nextID;
        return $services[$nextID];
    }

}
