<?php

namespace Bearns\Redis;

use think\Config;
use think\Container;
use Bearns\Support\Arr;

class RedisProvider
{
    /**
     * Register the service provider.
     *
     * @param Config $config
     * @return Redis
     */
    public static function __make(Config $config)
    {
        $config = $config->pull('redis');
        return new Redis(Arr::pull($config, 'client', 'predis'), $config);
    }
}
