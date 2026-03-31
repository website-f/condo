<?php

namespace FSPoster\App\Pages\Notification\Controllers;

use FSPoster\App\Pages\Notification\Services\NotificationService;
use FSPoster\App\Providers\Core\RestRequest;

class NotificationController
{
    private NotificationService $service;

    public function __construct(NotificationService $service)
    {
        $this->service = $service;
    }

    /**
     * @return array|string[]
     */
    public function list(RestRequest $request): array
    {
        $page = $request->param('page', 1, RestRequest::TYPE_INTEGER);
        $rowsPerPage = $request->param('rows_count', 10, RestRequest::TYPE_INTEGER);

        $notificationData = $this->service->getNotificationList($page, $rowsPerPage);

        return [
            'notifications' => $notificationData['data'],
            'count' => isset($notificationData['count']) ? (int)$notificationData['count'] : 0,
        ];
    }

    /**
     * @param RestRequest $request
     * @return array
     */
    public function markAsRead(RestRequest $request): array
    {
        $notificationId = $request->param('notification_id', 0, RestRequest::TYPE_INTEGER);

        $this->service->markAsRead($notificationId);

        return [];
    }

    /**
     * @return array
     */
    public function makeAllAsRead(): array
    {
        $this->service->markAllAsRead();

        return [];
    }

    /**
     * @return array
     */
    public function clear(): array
    {
        $this->service->clear();

        return [];
    }
}
