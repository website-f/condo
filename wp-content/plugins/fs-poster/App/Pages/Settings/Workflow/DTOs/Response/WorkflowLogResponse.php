<?php

namespace FSPoster\App\Pages\Settings\Workflow\DTOs\Response;

use JsonSerializable;

class WorkflowLogResponse implements JsonSerializable
{
    private int $id;
    private int $workflowId;
    private string $when;
    private string $driver;
    private string $workflowName;
    private int $createdAt;
    private string $status;
    private ?array $eventData = null;
    private ?array $actionData = null;
    private ?array $requestData = null;
    private ?array $responseData = null;
    private ?string $errorMsg = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getWorkflowId(): int
    {
        return $this->workflowId;
    }

    public function setWorkflowId(int $workflowId): void
    {
        $this->workflowId = $workflowId;
    }

    public function getWhen(): string
    {
        return $this->when;
    }

    public function setWhen(string $when): void
    {
        $this->when = $when;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function setDriver(string $driver): void
    {
        $this->driver = $driver;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'workflow_id' => $this->getWorkflowId(),
            'rule_name' => $this->getWorkflowName(),
            'when' => $this->getWhen(),
            'driver' => $this->getDriver(),
            'created_at' => $this->getCreatedAt(),
            'status' => $this->getStatus(),
            'event_data' => $this->getEventData(),
            'action_data' => $this->getActionData(),
            'request_data' => $this->getRequestData(),
            'response_data' => $this->getResponseData(),
            'error_msg' => $this->getErrorMsg(),
        ];
    }

    public function getWorkflowName(): string
    {
        return $this->workflowName;
    }

    public function setWorkflowName(string $workflowName): void
    {
        $this->workflowName = $workflowName;
    }

    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }

    public function setCreatedAt(int $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getEventData(): ?array
    {
        return $this->eventData;
    }

    public function setEventData(?array $eventData): void
    {
        $this->eventData = $eventData;
    }

    public function getActionData(): ?array
    {
        return $this->actionData;
    }

    public function setActionData(?array $actionData): void
    {
        $this->actionData = $actionData;
    }

    public function getRequestData(): ?array
    {
        return $this->requestData;
    }

    public function setRequestData(?array $requestData): void
    {
        $this->requestData = $requestData;
    }

    public function getResponseData(): ?array
    {
        return $this->responseData;
    }

    public function setResponseData(?array $responseData): void
    {
        $this->responseData = $responseData;
    }

    public function getErrorMsg(): ?string
    {
        return $this->errorMsg;
    }

    public function setErrorMsg(?string $errorMsg): void
    {
        $this->errorMsg = $errorMsg;
    }
}
