<?php

namespace Bearns\Redis;

use InvalidArgumentException;

/**
 * Class Redis
 * @package Bearns\Redis
 */
class Redis
{
    /**
     * The name of the default driver.
     *
     * @var string
     */
    protected $driver;

    /**
     * The Redis server configurations.
     *
     * @var array
     */
    protected $config;

    /**
     * The Redis connections.
     *
     * @var mixed
     */
    protected $connections;

    /**
     * Create a new Redis manager instance.
     *
     * @param  string $driver
     * @param  array $config
     * @return void
     */
    public function __construct($driver, array $config)
    {
        $this->driver = $driver;
        $this->config = $config;
    }

    /**
     * Get a Redis connection by name.
     *
     * @param  string|null $name
     * @return \Bearns\Redis\Connections\Connection
     */
    public function connection($name = null)
    {
        $name = $name ?: 'default';

        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }

        return $this->connections[$name] = $this->resolve($name);
    }

    /**
     * Resolve the given connection by name.
     *
     * @param  string|null $name
     * @return \Bearns\Redis\Connections\Connection
     *
     * @throws \InvalidArgumentException
     */
    public function resolve($name = null)
    {
        $name = $name ?: 'default';

        $options = isset($this->config['options']) ? $this->config['options'] : [];

        if (isset($this->config[$name])) {
            return $this->connector()->connect($this->config[$name], $options);
        }

        if (isset($this->config['clusters'][$name])) {
            return $this->resolveCluster($name);
        }

        throw new InvalidArgumentException(
            "Redis connection [{$name}] not configured."
        );
    }

    /**
     * Resolve the given cluster connection by name.
     *
     * @param  string $name
     * @return \Bearns\Redis\Connections\Connection
     */
    protected function resolveCluster($name)
    {
        $clusterOptions = isset($this->config['clusters']['options']) ? $this->config['clusters']['options'] : [];

        return $this->connector()->connectToCluster(
            $this->config['clusters'][$name], $clusterOptions, isset($this->config['options']) ? $this->config['options'] : []
        );
    }

    /**
     * Get the connector instance for the current driver.
     *
     * @return \Bearns\Redis\Connectors\PhpRedisConnector|\Bearns\Redis\Connectors\PredisConnector
     */
    protected function connector()
    {
        switch ($this->driver) {
            case 'predis':
                return new Connectors\PredisConnector;
            case 'phpredis':
                return new Connectors\PhpRedisConnector;
        }
    }

    /**
     * Return all of the created connections.
     *
     * @return array
     */
    public function connections()
    {
        return $this->connections;
    }

    /**
     * Pass methods onto the default Redis connection.
     *
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->connection()->{$method}(...$parameters);
    }
}
