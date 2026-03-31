<?php

namespace FSPoster\App\Providers\Common;

use FSPoster\App\Models\Workflow;
use FSPoster\App\Pages\Settings\Workflow\Services\WorkflowActionService;
use FSPoster\App\Pages\Settings\Workflow\Services\WorkflowService;
use FSPoster\App\Providers\Common\ShortCodeServiceForWorkflow as ShortCodeService;
use FSPoster\App\Providers\Core\Container;

class WorkflowEventsManager
{
    /**
     * @var WorkflowEvent[]
     */
    public $workflowEvents = [];

    /**
     * @var bool
     */
    private $isEnabled = true;

    /**
     * @var ShortCodeService
     */
    private $shortcodeService;

    /**
     * @var WorkflowDriversManager
     */
    private $driverManager;
    private WorkflowService $service;
    private WorkflowActionService $actionService;

    public function __construct()
    {
        $this->service = Container::get(WorkflowService::class);
        $this->actionService = Container::get(WorkflowActionService::class);
    }

    /**
     * Enable/disable all workflow events completely.
     * Returns previous state.
     * @param $enabled
     * @return bool
     */
    public function setEnabled($enabled): bool
    {
        $previousValue = $this->isEnabled();
        $this->isEnabled = $enabled;

        return $previousValue;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * @param $key
     * @param $instance
     * @return WorkflowEvent
     */
    public function register($key, $instance): WorkflowEvent
    {
        $this->workflowEvents[ $key ] = $instance;

        return $this->workflowEvents[ $key ];
    }

    /**
     * @return WorkflowEvent[]
     */
    public function getAll(): array
    {
        return $this->workflowEvents;
    }

    public function trigger($eventKey, $params, $filterClosure = false): void
    {
        if ($this->isEnabled() === false) {
            return;
        }

        if (! array_key_exists($eventKey, $this->workflowEvents)) {
            return;
        }

        $workflows = $this->service->getByEventKey($eventKey);

        if (is_callable($filterClosure)) {
            $workflows = array_filter($workflows, $filterClosure);
        }

        foreach ($workflows as $workflow) {
            /**
             * @var Workflow $workflow
             */
            $actions = $this->actionService->getActiveActionsByWorkflowId($workflow->id);

            foreach ($actions as $action) {
                $driver = $this->getDriverManager()->get($action->getDriver());

                if ($driver === null) {
                    continue;
                }

                $driver->handle($params, $action, $this->getShortcodeService());
            }
        }
    }

    /**
     * @param $key
     * @return WorkflowEvent
     */
    public function get($key): WorkflowEvent
    {
        if (! array_key_exists($key, $this->workflowEvents)) {
            $this->workflowEvents[ $key ] = new WorkflowEvent($key);
        }

        return $this->workflowEvents[ $key ];
    }

    /**
     * @return WorkflowDriversManager
     */
    public function getDriverManager(): WorkflowDriversManager
    {
        return $this->driverManager;
    }

    /**
     * @param WorkflowDriversManager $driverManager
     */
    public function setDriverManager(WorkflowDriversManager $driverManager): void
    {
        $this->driverManager = $driverManager;
    }

    /**
     * @return ShortCodeService
     */
    public function getShortcodeService(): ShortCodeService
    {
        return $this->shortcodeService;
    }

    /**
     * @param ShortCodeService $shortcodeService
     */
    public function setShortcodeService(ShortCodeService $shortcodeService): void
    {
        $this->shortcodeService = $shortcodeService;
    }

    /**
     * @return array
     */
    public function getList(): array
    {
        if (empty($this->workflowEvents)) {
            return [];
        }

        return array_keys($this->workflowEvents);
    }
}
