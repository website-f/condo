<?php

namespace FSPoster\App\Pages\Notification\Registerer\NotificationWorkflowEvents;

use FSPoster\App\Pages\Notification\Registerer\NotificationWorkflowEvent;
use FSPoster\App\Providers\Core\Container;
use FSPoster\App\Providers\Schedules\ScheduleService;

class ScheduleFailedNotificationWorkflowEvent implements NotificationWorkflowEvent
{
    private string $actionType = 'schedule_info';
    private ?string $actionData = null;
    private string $entityName = 'schedule_id';

    /**
     * @return string
     */
    public function getActionType(): string
    {
        return $this->actionType;
    }

    /**
     * @return string|null
     */
    public function getActionData(): ?string
    {
       return $this->actionData;
    }

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function setActionData(int $scheduleId): void
    {
        $scheduleService = Container::get(ScheduleService::class);
        $schedule = $scheduleService->get($scheduleId);

        if ($schedule === null) {
            return;
        }

        $actionData = [
            'schedule_id' => $schedule->id,
            'group_id' => $schedule->group_id,
        ];

        $this->actionData = json_encode($actionData);
    }
}
