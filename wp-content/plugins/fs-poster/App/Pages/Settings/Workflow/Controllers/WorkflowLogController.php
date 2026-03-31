<?php

namespace FSPoster\App\Pages\Settings\Workflow\Controllers;

use FSPoster\App\Pages\Settings\Workflow\Mappers\WorkflowLogMapper;
use FSPoster\App\Pages\Settings\Workflow\Services\WorkflowLogService;
use FSPoster\App\Providers\Core\Container;
use FSPoster\App\Providers\Core\RestRequest;
use ReflectionException;
use RuntimeException;

class WorkflowLogController
{
    private WorkflowLogService $service;

    public function __construct(WorkflowLogService $service)
    {
        $this->service = $service;
    }

    /**
     * @return array{workflowLogs: array}
     */
    public function getAll(RestRequest $request): array
    {
        $page = $request->param('page', 1, RestRequest::TYPE_INTEGER);

        $workflowLogs = $this->service->getAll($page);

        return [
            'workflowLogs' => $workflowLogs,
            'total' => $this->service->getLogsCount()
        ];
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function get(RestRequest $request): array
    {
        $id = $request->require('id', RestRequest::TYPE_INTEGER, fsp__('Id is required'));

        $workflowLogData = $this->service->get($id);

        if ($workflowLogData === null) {
            throw new RuntimeException(fsp__('Log not found'));
        }

        $mapper = Container::get(WorkflowLogMapper::class);
        $workflowLog = $mapper->toResponse($workflowLogData);

        return ['workflowLog' => $workflowLog];
    }

    /**
     * @return array
     */
    public function clear(): array
    {
        $this->service->clear();

        return [];
    }

    public function delete(RestRequest $request): array
    {
        $id = $request->param('id', null, RestRequest::TYPE_INTEGER);

        if ($id === null) {
            throw new RuntimeException(fsp__('Id is required'));
        }

        $log = $this->service->get($id);

        if ($log === null) {
            throw new RuntimeException(fsp__('Log not found'));
        }

        $this->service->delete($id);

        return [];
    }

    public function retry(RestRequest $request): array
    {
        $id = $request->param('id', null, RestRequest::TYPE_INTEGER);

        if ($id === null) {
            throw new RuntimeException(fsp__('Id is required'));
        }

        $this->service->retry($id);

        return [];
    }
}
