<?php

namespace Bearns\Redis\Connectors;

use Redis;
use RedisCluster;
use Bearns\Support\Arr;
use Bearns\Redis\Connections\PhpRedisConnection;
use Bearns\Redis\Connections\PhpRedisClusterConnection;

class PhpRedisConnector
{
    /**
     * Create a new clustered Predis connection.
     *
     * @param  array $config
     * @param  array $options
     * @return \Bearns\Redis\Connections\PhpRedisConnection
     */
    public function connect(array $config, array $options)
    {
        return new PhpRedisConnection($this->createClient(array_merge(
            $config, $options, Arr::pull($config, 'options', [])
        )));
    }

    /**
     * Create a new clustered Predis connection.
     *
     * @param  array $config
     * @param  array $clusterOptions
     * @param  array $options
     * @return \Bearns\Redis\Connections\PhpRedisClusterConnection
     */
    public function connectToCluster(array $config, array $clusterOptions, array $options)
    {
        $options = array_merge($options, $clusterOptions, Arr::pull($config, 'options', []));

        return new PhpRedisClusterConnection($this->createRedisClusterInstance(
            array_map([$this, 'buildClusterConnectionString'], $config), $options
        ));
    }

    /**
     * Build a single cluster seed string from array.
     *
     * @param  array $server
     * @return string
     */
    protected function buildClusterConnectionString(array $server)
    {
        return $server['host'] . ':' . $server['port'] . '?' . http_build_query(Arr::only($server, [
                'database', 'password', 'prefix', 'read_timeout',
            ]));
    }

    /**
     * Create the Redis client instance.
     *
     * @param  array $config
     * @return \Redis
     */
    protected function createClient(array $config)
    {
        return tap(new Redis, function ($client) use ($config) {
            $this->establishConnection($client, $config);

            if (!empty($config['password'])) {
                $client->auth($config['password']);
            }

            if (!empty($config['database'])) {
                $client->select($config['database']);
            }

            if (!empty($config['prefix'])) {
                $client->setOption(Redis::OPT_PREFIX, $config['prefix']);
            }

            if (!empty($config['read_timeout'])) {
                $client->setOption(Redis::OPT_READ_TIMEOUT, $config['read_timeout']);
            }
        });
    }

    /**
     * Establish a connection with the Redis host.
     *
     * @param  \Redis $client
     * @param  array $config
     * @return void
     */
    protected function establishConnection($client, array $config)
    {
        $client->{(isset($config['persistent']) ? $config['persistent'] : false) === true ? 'pconnect' : 'connect'}(
            $config['host'], $config['port'], Arr::get($config, 'timeout', 0)
        );
    }

    /**
     * Create a new redis cluster instance.
     *
     * @param  array $servers
     * @param  array $options
     * @return \RedisCluster
     */
    protected function createRedisClusterInstance(array $servers, array $options)
    {
        return new RedisCluster(
            null,
            array_values($servers),
            isset($options['timeout']) ? $options['timeout'] : 0,
            isset($options['read_timeout']) ? $options['read_timeout'] : 0,
            isset($options['persistent']) && $options['persistent']
        );
    }
}
