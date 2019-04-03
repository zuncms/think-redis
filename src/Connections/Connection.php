<?php

namespace Zuncms\ThinkRedis\Connections;

use Closure;
use think\Event;
use Zuncms\ThinkRedis\Events\CommandExecuted;
use Zuncms\ThinkRedis\Limiters\DurationLimiterBuilder;
use Zuncms\ThinkRedis\Limiters\ConcurrencyLimiterBuilder;

abstract class Connection
{
    /**
     * The Predis client.
     *
     * @var \Predis\Client
     */
    protected $client;

    /**
     * The Redis connection name.
     *
     * @var string|null
     */
    protected $name;

    /**
     * The event dispatcher instance.
     *
     * @var \think\Event
     */
    protected $event;

    /**
     * Subscribe to a set of given channels for messages.
     *
     * @param array|string $channels
     * @param \Closure     $callback
     * @param string       $method
     */
    abstract public function createSubscription($channels, Closure $callback, $method = 'subscribe');

    /**
     * Funnel a callback for a maximum number of simultaneous executions.
     *
     * @param string $name
     *
     * @return \Zuncms\ThinkRedis\Limiters\ConcurrencyLimiterBuilder
     */
    public function funnel($name)
    {
        return new ConcurrencyLimiterBuilder($this, $name);
    }

    /**
     * Throttle a callback for a maximum number of executions over a given duration.
     *
     * @param string $name
     *
     * @return \Zuncms\ThinkRedis\Limiters\DurationLimiterBuilder
     */
    public function throttle($name)
    {
        return new DurationLimiterBuilder($this, $name);
    }

    /**
     * Get the underlying Redis client.
     *
     * @return mixed
     */
    public function client()
    {
        return $this->client;
    }

    /**
     * Subscribe to a set of given channels for messages.
     *
     * @param array|string $channels
     * @param \Closure     $callback
     */
    public function subscribe($channels, Closure $callback)
    {
        return $this->createSubscription($channels, $callback, __FUNCTION__);
    }

    /**
     * Subscribe to a set of given channels with wildcards.
     *
     * @param array|string $channels
     * @param \Closure     $callback
     */
    public function psubscribe($channels, Closure $callback)
    {
        return $this->createSubscription($channels, $callback, __FUNCTION__);
    }

    /**
     * Run a command against the Redis database.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function command($method, array $parameters = [])
    {
        $start = microtime(true);

        $result = $this->client->{$method}(...$parameters);

        $time = round((microtime(true) - $start) * 1000, 2);

        if (isset($this->event)) {
            $this->event(new CommandExecuted($method, $parameters, $time, $this));
        }

        return $result;
    }

    /**
     * Fire the given event if possible.
     *
     * @param mixed $event
     */
    protected function event($event)
    {
        if (isset($this->event)) {
            $this->event->trigger($event, $event->toArray());
        }
    }

    /**
     * Register a Redis command listener with the connection.
     *
     * @param \Closure $callback
     */
    public function listen(Closure $callback)
    {
        if (isset($this->event)) {
            $this->event->listen(CommandExecuted::class, $callback);
        }
    }

    /**
     * Get the connection name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the connections name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the event dispatcher used by the connection.
     *
     * @return \think\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set the event dispatcher instance on the connection.
     *
     * @param \think\Event $event
     */
    public function setEventDispatcher(Event $event)
    {
        $this->event = $event;
    }

    /**
     * Unset the event dispatcher instance on the connection.
     */
    public function unsetEventDispatcher()
    {
        $this->event = null;
    }

    /**
     * Pass other method calls down to the underlying client.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->command($method, $parameters);
    }
}
