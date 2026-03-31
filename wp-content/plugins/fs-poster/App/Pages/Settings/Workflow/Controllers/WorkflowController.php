<?php

namespace FSPoster\App\Pages\Settings\Workflow\Controllers;

use FSPoster\App\Pages\Settings\Workflow\DTOs\Request\WorkFlowActionRequest;
use FSPoster\App\Pages\Settings\Workflow\DTOs\Request\WorkflowRequest;
use FSPoster\App\Pages\Settings\Workflow\Mappers\WorkflowMapper;
use FSPoster\App\Providers\Common\WorkflowDriversManager;
use FSPoster\App\Providers\Common\WorkflowEventsManager;
use FSPoster\App\Providers\Core\Container;
use FSPoster\App\Providers\Core\RestRequest;
use FSPoster\App\Pages\Settings\Workflow\Services\WorkflowService;
use FSPoster\App\Pages\Settings\Workflow\Services\WorkflowActionService;
use ReflectionException;

class WorkflowController
{
    private WorkflowDriversManager $workflowDriversManager;
    private WorkflowEventsManager $workflowEventsManager;
    private WorkflowService $service;
    private WorkflowActionService $actionService;

    public function __construct(WorkflowEventsManager $workflowEventsManager, WorkflowActionService $actionService, WorkflowService $service)
    {
        $this->workflowEventsManager = $workflowEventsManager;
        $this->workflowDriversManager = $this->workflowEventsManager->getDriverManager();
        $this->actionService = $actionService;
        $this->service = $service;
    }

    /**
     * @return array{workflows: array}
     * @throws ReflectionException
     */
    public function getAll(): array
    {
        $workflows = $this->service->getAll();

        return ['workflows' => $workflows];
    }

    /**
     * @return array
     */
    public function getDrivers(): array
    {
        return ['drivers' => array_keys($this->workflowDriversManager->getList())];
    }

    /**
     * @return array
     */
    public function getEvents(): array
    {
        return ['events' => array_keys($this->workflowEventsManager->getAll())];
    }

    /**
     * @return array{workflow_id: int}
     */
    public function create(RestRequest $request): array
    {
        $name = $request->param('name', null, RestRequest::TYPE_STRING);
        $event = $request->param('event', null, RestRequest::TYPE_STRING);
        $action = $request->param('action', null, RestRequest::TYPE_STRING);
        $isActive = $request->param('is_active', 0, RestRequest::TYPE_INTEGER);

        if ($name === null || $event === null || $action === null) {
            throw new \RuntimeException(fsp__('Invalid request parameters'));
        }

        $workflowDriver = $this->workflowDriversManager->get($action);

        if ($workflowDriver === null) {
            throw new \RuntimeException(fsp__('Driver not found'));
        }

        if (!array_key_exists($event, $this->workflowEventsManager->getAll())) {
            throw new \RuntimeException(fsp__('Event not found'));
        }

        $workflowRequest = new WorkflowRequest();

        $workflowRequest->setName($name);
        $workflowRequest->setWhen($event);
        $workflowRequest->setIsActive($isActive);

        $id = $this->service->create($workflowRequest);

        $workflowActionRequest = new WorkFlowActionRequest();

        $workflowActionRequest->setWorkflowId($id);
        $workflowActionRequest->setDriver($action);
        $workflowActionRequest->setIsActive(1);

        $this->actionService->create($workflowActionRequest);

        return ['workflow_id' => $id];
    }

    /**
     * @return array{workflow_id: int}
     */
    public function update(RestRequest $request): array
    {
        $id = $request->require('id', RestRequest::TYPE_INTEGER);
        $name = $request->param('name', null, RestRequest::TYPE_STRING);
        $isActive = $request->param('is_active', null, RestRequest::TYPE_INTEGER);

        if ($name === null && $isActive === null) {
            throw new \RuntimeException(fsp__('No fields to update'));
        }

        $workflowRequest = new WorkflowRequest();

        if ($name !== null) {
            $workflowRequest->setName($name);
        }

        if ($isActive !== null) {
            $workflowRequest->setIsActive($isActive);
        }

        $this->service->update($id, $workflowRequest);

        return ['workflow_id' => $id];
    }

    /**
     * @return array{actions: array, events_manager: WorkflowEventsManager}
     * @throws ReflectionException
     */
    public function get(RestRequest $request): array
    {
        $id = $request->param('workflow_id',null, RestRequest::TYPE_INTEGER);

        if ($id === null) {
            throw new \RuntimeException(fsp__('Workflow id is required.'));
        }

        $workflowData = $this->service->get($id);

        if ($workflowData === null) {
            throw new \RuntimeException(fsp__('Workflow not found'));
        }

        $mapper = Container::get(WorkflowMapper::class);

        $workflow = $mapper->toResponse($workflowData);

        $workflow->setActions(array_map(function ($data) {
            return [
                'id' => (int)$data->id,
                'driver' => $data->driver,
                'data' => $data->data
            ];
        }, $this->actionService->getActionsByWorkflowId($workflow->getId())));

       return ['workflow' => $workflow];
    }

    public function delete(RestRequest $request): array
    {
        $id = $request->param('id', null, RestRequest::TYPE_INTEGER);

        if ($id === null) {
            throw new \RuntimeException(fsp__('Workflow id is required.'));
        }

        $workflow = $this->service->get($id);

        if ($workflow === null) {
            throw new \RuntimeException(fsp__('Workflow not found.'));
        }

        $this->service->delete($id);

        return [];
    }

    /**
     * @return array
     */
    public function getWpUsers(): array
    {
        return ['users' => $this->service->getWpUsers()];
    }
}