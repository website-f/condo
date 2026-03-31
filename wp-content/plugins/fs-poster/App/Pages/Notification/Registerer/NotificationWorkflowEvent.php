<?php

namespace FSPoster\App\Pages\Notification\Registerer;

interface NotificationWorkflowEvent
{
    public function getActionType(): string;
    public function getActionData(): ?string;
    public function getEntityName(): string;
    public function setActionData(int $entityId): void;
}
