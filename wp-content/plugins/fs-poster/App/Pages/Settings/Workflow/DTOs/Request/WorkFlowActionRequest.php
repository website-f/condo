<?php

namespace FSPoster\App\Pages\Settings\Workflow\DTOs\Request;

class WorkFlowActionRequest
{
    private int $workflowId;
    private string $driver;
    private ?string $data = null;
    private int $isActive;

    public function getWorkflowId(): int
    {
        return $this->workflowId;
    }

    public function setWorkflowId(int $workflowId): void
    {
        $this->workflowId = $workflowId;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function setDriver(string $driver): void
    {
        $this->driver = $driver;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data): void
    {
        $this->data = $data;
    }

    public function getIsActive(): int
    {
        return $this->isActive;
    }

    public function setIsActive(int $isActive): void
    {
        $this->isActive = $isActive;
    }
}