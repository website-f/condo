<?php

namespace FSPoster\App\Pages\Settings\Workflow\DTOs\Response;

class WorkflowResponse implements \JsonSerializable
{
    private int $id;
    private string $name;
    private string $when;
    private ?array $data;
    private bool $isActive;
    private ?array $actions;

    /**
     * @return array{id: int, name: string, when: string, data: string, is_active: bool}
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'when' => $this->getWhen(),
            'data' => $this->getData(),
            'is_active' => $this->isActive(),
            'actions' => $this->getActions(),
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
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

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(string $data): void
    {
        $data = json_decode($data, true);
        $this->data = $data;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getActions(): ?array
    {
        return $this->actions;
    }

    public function setActions(?array $actions): void
    {
        $this->actions = $actions;
    }
}