<?php

namespace FSPoster\App\Pages\Notification\Services;

use FSPoster\App\Pages\Notification\DTOs\Request\NotificationRequest;
use FSPoster\App\Pages\Notification\Mappers\NotificationMapper;
use FSPoster\App\Pages\Notification\Repositories\NotificationRepository;

class NotificationService
{
    private NotificationRepository $repository;

    public function __construct(NotificationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param int $page
     * @param int $rowsPerPage
     * @return array
     */
    public function getNotificationList(int $page, int $rowsPerPage): array
    {
        $notificationData = $this->repository->getNotifications($page, $rowsPerPage);

        $count = $notificationData['count'];
        $notifications = $notificationData['data'];

        $notifications = (new NotificationMapper())->toListResponse($notifications);

        array_map(function ($notification) {
            $user = get_userdata($notification->getUserId());
            $notification->setUserLogin($user->user_login ?? null);
        }, $notifications);

        return [
            'count' => $count,
            'data' => $notifications
        ];
    }

    /**
     * @param int $notificationId
     * @return void
     */
    public function markAsRead(int $notificationId): void
    {
        if ($notificationId <= 0) {
            throw new \RuntimeException(fsp__('Invalid notification ID'));
        }

        $notification = $this->repository->get($notificationId);

        if (!$notification) {
            throw new \RuntimeException(fsp__('Notification not found'));
        }

        $this->repository->markAsReadById($notificationId);
    }

    /**
     * @return void
     */
    public function markAllAsRead(): void
    {
        $this->repository->markAllAsRead();
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->repository->deleteNotifications();
    }

    public function createNotification(NotificationRequest $request): int
    {
        $data = [
            'user_id' => $request->getUserId(),
            'type' => $request->getType(),
            'title' => $request->getTitle(),
            'message' => $request->getMessage(),
            'action_type' => $request->getActionType(),
            'action_data' => $request->getActionData()
        ];

        return $this->repository->create($data);
    }

    public function getUnreadCount(): int
    {
        return $this->repository->getUnreadCount();
    }
}
