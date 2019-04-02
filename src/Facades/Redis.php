<?php

namespace Zuncms\ThinkRedis\Facades;

use think\Facade;

class App extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）.
     *
     * @return string
     */
    protected static function getFacadeClass()
    {
        return 'redis';
    }
}
