<?php

namespace FSPoster\App\Pages\Notification;

use FSPoster\App\Pages\Notification\Controllers\NotificationController;
use FSPoster\App\Pages\Notification\Registerer\NotificationWorkflowEvents\ScheduleFailedNotificationWorkflowEvent;
use FSPoster\App\Pages\Notification\Repositories\NotificationRepository;
use FSPoster\App\Pages\Notification\Services\NotificationService;
use FSPoster\App\Providers\Core\Container;
use FSPoster\App\Providers\Helpers\PluginHelper;

class NotificationModule
{
    public static function init(): void
    {
        self::registerDependencies();
        self::registerHooks();
    }

    private static function registerDependencies(): void
    {
        Container::addBulk([
            NotificationController::class,
            NotificationService::class,
            NotificationRepository::class,
            ScheduleFailedNotificationWorkflowEvent::class,
        ]);
    }

    private static function registerHooks(): void
    {
        add_filter('fsp_admin_menu_title', function ($title) {
            if ( ! PluginHelper::isPluginActivated() )
            {
                return $title;
            }

            $notificationService = Container::get(NotificationService::class);

            $count = $notificationService->getUnreadCount();

            $title .= $count > 0 ? ' (' . ($count > 99 ? '99+' : $count) . ')' : '';

            return $title;
        });
    }
}