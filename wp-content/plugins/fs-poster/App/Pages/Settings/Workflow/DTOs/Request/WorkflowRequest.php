<?php

namespace FSPoster\App\Pages\Settings\Workflow\DTOs\Request;

class WorkflowRequest
{
    private ?string $name = null;
    private string $when = '';
    private string $data = '';
    private ?int $isActive = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getWhen(): string
    {
        return $this->when;
    }

    public function setWhen(string $when): void
    {
        $this->when = $when;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function setData(?string $data): void
    {
        $this->data = $data;
    }

    public function getIsActive(): ?int
    {
        return $this->isActive;
    }

    public function setIsActive(int $isActive): void
    {
        $this->isActive = $isActive;
    }
}