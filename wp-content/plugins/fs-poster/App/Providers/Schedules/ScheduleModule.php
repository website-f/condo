<?php

namespace FSPoster\App\Providers\Schedules;

use FSPoster\App\Providers\Core\Container;
use FSPoster\App\Providers\Schedules\Repositories\ScheduleRepository;

class ScheduleModule
{
    public static function init(): void
    {
        self::registerDependencies();
    }

    private static function registerDependencies(): void
    {
        Container::addBulk([
            ScheduleService::class,
            ScheduleRepository::class,
        ]);
    }
}