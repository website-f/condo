<?php

namespace FSPoster\App\Pages\Notification\Repositories;

use FSPoster\App\Models\Notification;
use FSPoster\App\Providers\DB\Collection;
use FSPoster\App\Providers\Helpers\Date;

class NotificationRepository
{
    /**
     * @param int $page
     * @param int $rowsPerPage
     * @return array
     */
    public function getNotifications(int $page = 0, int $rowsPerPage = 0): array
    {
        $query = Notification::query()
                ->orderBy('id DESC');

        $count = $query->count();

        if (!empty($page)) {
            $query->offset(($page - 1) * $rowsPerPage);
        }
        if (!empty($rowsPerPage)) {
            $query->limit($rowsPerPage);
        }

        $notifications = $query->fetchAll();

        return [
            'data' => $notifications,
            'count' => $count
        ];
    }

    /**
     * @param int $notificationId
     * @return Collection|null
     */
    public function get(int $notificationId): ?Collection
    {
        return Notification::query()->where('id', $notificationId)->whereIsNull('read_at')->fetch();
    }

    public function markAsReadById(int $notificationId): void
    {
        Notification::query()->where('id', $notificationId)->whereIsNull('read_at')->update([
            'read_at' => Date::dateTimeSQL()
        ]);
    }

    /**
     * @return void
     */
    public function markAllAsRead(): void
    {
        Notification::query()->whereIsNull('read_at')->update([
            'read_at' => Date::dateTimeSQL()
        ]);
    }

    /**
     * @return void
     */
    public function deleteNotifications(): void
    {
        Notification::query()->delete();
    }

    public function create(array $data): int
    {
        Notification::query()->withoutGlobalScope('user_id')->insert($data);

        return Notification::lastId();
    }

    public function getUnreadCount(): int
    {
        return Notification::query()->whereIsNull('read_at')->count();
    }
}

