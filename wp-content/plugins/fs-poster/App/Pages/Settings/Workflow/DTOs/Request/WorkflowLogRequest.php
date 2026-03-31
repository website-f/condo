<?php

namespace FSPoster\App\Pages\Settings\Workflow\DTOs\Request;

class WorkflowLogRequest
{
    private int $workflow_id;
    private string $when;
    private string $driver;
    private ?string $requestData = null;
    private ?string $responseData = null;
    private string $status;
    private ?string $errorMsg = null;

    public function getWorkflowId(): int
    {
        return $this->workflow_id;
    }

    public function setWorkflowId(int $workflow_id): void
    {
        $this->workflow_id = $workflow_id;
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

    public function getRequestData(): ?string
    {
        return $this->requestData;
    }

    public function setRequestData(?string $requestData): void
    {
        $this->requestData = $requestData;
    }

    public function getResponseData(): ?string
    {
        return $this->responseData;
    }

    public function setResponseData(?string $responseData): void
    {
        $this->responseData = $responseData;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
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
