<?php

namespace FSPoster\App\Pages\Settings\Workflow\DTOs\Response;

class WorkflowActionResponse
{
    private array $data;
    private int $workflowId;
    private string $slug;
    private string $driver;

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getWorkflowId(): int
    {
        return $this->workflowId;
    }

    public function setWorkflowId(int $workflowId): void
    {
        $this->workflowId = $workflowId;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function setDriver(string $driver): void
    {
        $this->driver = $driver;
    }
}
