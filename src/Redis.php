<?php

namespace Zuncms\ThinkRedis;

use think\App;
use Zuncms\Helper\Arr;
use InvalidArgumentException;
use Zuncms\ThinkRedis\Contracts\Factory;
use Zuncms\ThinkRedis\Connections\Connection;

class Redis implements Factory
{
    /**
     * The application instance.
     *
     * @var \think\App
     */
    protected $app;

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
     * Indicates whether event dispatcher is set on connections.
     *
     * @var bool
     */
    protected $event = false;

    /**
     * Create a new Redis manager instance.
     *
     * @param \think\App $app
     * @param string     $driver
     * @param array      $config
     */
    public function __construct($app, $driver, array $config)
    {
        $this->app = $app;
        $this->driver = $driver;
        $this->config = $config;
    }

    /**
     * Get a Redis connection by name.
     *
     * @param string|null $name
     *
     * @return \Zuncms\ThinkRedis\Connections\Connection
     */
    public function connection($name = null)
    {
        $name = $name ?: 'default';

        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }

        return $this->connections[$name] = $this->configure(
            $this->resolve($name), $name
        );
    }

    /**
     * Resolve the given connection by name.
     *
     * @param string|null $name
     *
     * @return \Zuncms\ThinkRedis\Connections\Connection
     *
     * @throws \InvalidArgumentException
     */
    public function resolve($name = null)
    {
        $name = $name ?: 'default';

        $options = $this->config['options'] ?? [];

        if (isset($this->config[$name])) {
            return $this->connector()->connect($this->config[$name], $options);
        }

        if (isset($this->config['clusters'][$name])) {
            return $this->resolveCluster($name);
        }

        throw new InvalidArgumentException("Redis connection [{$name}] not configured.");
    }

    /**
     * Resolve the given cluster connection by name.
     *
     * @param string $name
     *
     * @return \Zuncms\ThinkRedis\Connections\Connection
     */
    protected function resolveCluster($name)
    {
        $clusterOptions = $this->config['clusters']['options'] ?? [];

        return $this->connector()->connectToCluster(
            $this->config['clusters'][$name], $clusterOptions, $this->config['options'] ?? []
        );
    }

    /**
     * Configure the given connection to prepare it for commands.
     *
     * @param \Zuncms\ThinkRedis\Connections\Connection $connection
     * @param string                                    $name
     *
     * @return \Zuncms\ThinkRedis\Connections\Connection
     */
    protected function configure(Connection $connection, $name)
    {
        $connection->setName($name);

        if ($this->event && $this->app->bound('event')) {
            $connection->setEventDispatcher($this->app->make('event'));
        }

        return $connection;
    }

    /**
     * Get the connector instance for the current driver.
     *
     * @return \Zuncms\ThinkRedis\Connectors\PhpRedisConnector|\Zuncms\ThinkRedis\Connectors\PredisConnector
     */
    protected function connector()
    {
        switch ($this->driver) {
            case 'predis':
                return new Connectors\PredisConnector();
            case 'phpredis':
                return new Connectors\PhpRedisConnector();
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
     * Enable the firing of Redis command event.
     */
    public function enableEvent()
    {
        $this->event = true;
    }

    /**
     * Disable the firing of Redis command event.
     */
    public function disableEvent()
    {
        $this->event = false;
    }

    /**
     * Pass methods onto the default Redis connection.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->connection()->{$method}(...$parameters);
    }

    /**
     * Register the service provider.
     *
     * @param \think\App $app
     *
     * @return \Zuncms\ThinkRedis\Redis
     */
    public static function __make(App $app)
    {
        $config = $app->config->get('redis', []);

        return new self($app, Arr::pull($config, 'client', 'predis'), $config);
    }
}
