<?php

namespace Zuncms\ThinkRedis\Contracts;

interface Factory
{
    /**
     * Get a Redis connection by name.
     *
     * @param string $name
     *
     * @return \Zuncms\ThinkRedis\Connections\Connection
     */
    public function connection($name = null);
}
