<?php

namespace Zuncms\ThinkRedis\Events;

class CommandExecuted
{
    /**
     * The Redis command that was executed.
     *
     * @var string
     */
    public $command;

    /**
     * The array of command parameters.
     *
     * @var array
     */
    public $parameters;

    /**
     * The number of milliseconds it took to execute the command.
     *
     * @var float
     */
    public $time;

    /**
     * The Redis connection instance.
     *
     * @var \Zuncms\ThinkRedis\Connections\Connection
     */
    public $connection;

    /**
     * The Redis connection name.
     *
     * @var string
     */
    public $connectionName;

    /**
     * Create a new event instance.
     *
     * @param string                                    $command
     * @param array                                     $parameters
     * @param float|null                                $time
     * @param \Zuncms\ThinkRedis\Connections\Connection $connection
     */
    public function __construct($command, $parameters, $time, $connection)
    {
        $this->time = $time;
        $this->command = $command;
        $this->parameters = $parameters;
        $this->connection = $connection;
        $this->connectionName = $connection->getName();
    }

    public function toArray()
    {
        return [
            'time' => $this->time,
            'command' => $this->command,
            'parameters' => $this->parameters,
            'connection' => $this->connection,
            'connectionName' => $this->connectionName,
        ];
    }
}
