<?php

namespace FSPoster\App\Pages\Notification\Registerer;

use FSPoster\App\Providers\Core\Container;
use RuntimeException;

class NotificationWorkflowEventRegisterer
{
    private static array $instances = [];

    /**
     * @param string $eventName
     * @param class-string $instance
     * @return void
     */
    public static function registerEvents(string $eventName, string $instance): void
    {
        $interfaces = class_implements($instance);

        if (!in_array(NotificationWorkflowEvent::class, $interfaces, true)) {
            throw new RuntimeException(fsp__('Class %s does not implement NotificationWorkflowEvent', $instance));
        }

        self::$instances[$eventName] = $instance;
    }

    public static function getEventInstance(string $eventName): ?NotificationWorkflowEvent
    {
        return isset(self::$instances[$eventName]) ? Container::get(self::$instances[$eventName]) : null;
    }
}
