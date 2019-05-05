<?php

namespace Appocular\Differ\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Appocular\Differ\Events\ExampleEvent' => [
            'Appocular\Differ\Listeners\ExampleListener',
        ],
    ];
}
