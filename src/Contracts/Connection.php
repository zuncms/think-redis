<?php

namespace Zuncms\ThinkRedis\Contracts;

use Closure;

interface Connection
{
    /**
     * Subscribe to a set of given channels for messages.
     *
     * @param array|string $channels
     * @param \Closure     $callback
     */
    public function subscribe($channels, Closure $callback);

    /**
     * Subscribe to a set of given channels with wildcards.
     *
     * @param array|string $channels
     * @param \Closure     $callback
     */
    public function psubscribe($channels, Closure $callback);

    /**
     * Run a command against the Redis database.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function command($method, array $parameters = []);
}
