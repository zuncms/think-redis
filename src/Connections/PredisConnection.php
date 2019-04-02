<?php

namespace Zuncms\ThinkRedis\Connections;

use Closure;
use Zuncms\ThinkRedis\Contracts\Connection as ConnectionContract;

class PredisConnection extends Connection implements ConnectionContract
{
    /**
     * Create a new Predis connection.
     *
     * @param \Predis\Client $client
     */
    public function __construct($client)
    {
        $this->client = $client;
    }

    /**
     * Subscribe to a set of given channels for messages.
     *
     * @param array|string $channels
     * @param \Closure     $callback
     * @param string       $method
     */
    public function createSubscription($channels, Closure $callback, $method = 'subscribe')
    {
        $loop = $this->pubSubLoop();

        call_user_func_array([$loop, $method], (array) $channels);

        foreach ($loop as $message) {
            if ('message' === $message->kind || 'pmessage' === $message->kind) {
                call_user_func($callback, $message->payload, $message->channel);
            }
        }

        unset($loop);
    }
}
