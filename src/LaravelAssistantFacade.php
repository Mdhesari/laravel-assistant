<?php

namespace Mdhesari\LaravelAssistant;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mdhesari\LaravelAssistant\Skeleton\SkeletonClass
 */
class LaravelAssistantFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-assistant';
    }
}
