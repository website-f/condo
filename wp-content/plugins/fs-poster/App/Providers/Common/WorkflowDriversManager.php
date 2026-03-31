<?php

namespace FSPoster\App\Providers\Common;

class WorkflowDriversManager
{
    /**
     * @var WorkflowDriverInterface[]
     */
    private array $drivers = [];

    public function register(WorkflowDriverInterface $driverInstance): void
    {
        $driver = $driverInstance->getDriver();

        $this->drivers[ $driver ] = $driverInstance;
    }

    /**
     * @param string $driver
     * @return WorkflowDriverInterface|null
     */
    public function get($driver): ?WorkflowDriverInterface
    {
        return $this->drivers[$driver] ?? null;
    }

    /**
     * @return WorkflowDriverInterface[]
     */
    public function getList(): array
    {
        return $this->drivers;
    }
}
