<?php

namespace Zuncms\ThinkRedis;

use think\App;
use Zuncms\Helper\Arr;

class RedisProvider
{
    /**
     * Register the service provider.
     *
     * @param \think\App $app
     */
    public static function __make(App $app)
    {
        $app->instance('redis', function (App $app) {
            $config = $app->config->get('redis', []);

            return new Redis($app, Arr::pull($config, 'client', 'predis'), $config);
        });

        $app->bind('redis.connection', function ($app) {
            return $app['redis']->connection();
        });
    }
}
