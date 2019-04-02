<?php

namespace Zuncms\ThinkRedis\Connectors;

use Predis\Client;
use Zuncms\Helper\Arr;
use Zuncms\ThinkRedis\Connections\PredisConnection;
use Zuncms\ThinkRedis\Connections\PredisClusterConnection;

class PredisConnector
{
    /**
     * Create a new clustered Predis connection.
     *
     * @param array $config
     * @param array $options
     *
     * @return \Zuncms\ThinkRedis\Connections\PredisConnection
     */
    public function connect(array $config, array $options)
    {
        $formattedOptions = array_merge(
            ['timeout' => 10.0], $options, Arr::pull($config, 'options', [])
        );

        return new PredisConnection(new Client($config, $formattedOptions));
    }

    /**
     * Create a new clustered Predis connection.
     *
     * @param array $config
     * @param array $clusterOptions
     * @param array $options
     *
     * @return \Zuncms\ThinkRedis\Connections\PredisClusterConnection
     */
    public function connectToCluster(array $config, array $clusterOptions, array $options)
    {
        $clusterSpecificOptions = Arr::pull($config, 'options', []);

        return new PredisClusterConnection(new Client(array_values($config), array_merge(
            $options, $clusterOptions, $clusterSpecificOptions
        )));
    }
}
