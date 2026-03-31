<?php

namespace FSPoster\App\Pages\Settings\Workflow\Services;

use FSPoster\App\Models\WorkflowLog;
use FSPoster\App\Pages\Settings\Workflow\DTOs\Request\WorkflowLogRequest;
use FSPoster\App\Pages\Settings\Workflow\DTOs\Response\WorkflowActionResponse;
use FSPoster\App\Pages\Settings\Workflow\Mappers\WorkflowLogMapper;
use FSPoster\App\Pages\Settings\Workflow\Repositories\WorkflowLogRepository;
use FSPoster\App\Providers\Common\WorkflowDriversManager;
use FSPoster\App\Providers\Common\WorkflowEventsManager;
use FSPoster\App\Providers\Core\Container;
use FSPoster\App\Providers\DB\Collection;
use RuntimeException;

class WorkflowLogService
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';
    private WorkflowLogRepository $repository;
    private WorkflowLogMapper $mapper;

    public function __construct(WorkflowLogRepository $repository, WorkflowLogMapper $mapper)
    {
        $this->repository = $repository;
        $this->mapper = $mapper;
    }

    /**
     * @param int $page
     * @return array
     */
    public function getAll(int $page): array
    {
        $logs = $this->repository->getAll($page);
        return $this->mapper->toListResponse($logs);
    }

    /**
     * @param int $id
     * @return WorkflowLog|Collection|null
     */
    public function get(int $id)
    {
        return $this->repository->get($id);
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->repository->clear();
    }

    /**
     * @return int
     */
    public function getLogsCount(): int
    {
        return $this->repository->getLogsCount();
    }

    public function create(WorkflowLogRequest $request): void
    {
        $this->repository->create([
            'workflow_id' => $request->getWorkflowId(),
            'when' => $request->getWhen(),
            'driver' => $request->getDriver(),
            'request_data' => $request->getRequestData(),
            'response_data' => $request->getResponseData(),
            'status' => $request->getStatus(),
            'error_msg' => $request->getErrorMsg(),
        ]);
    }

    /**
     * @param int $id
     * @return void
     */
    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }

    public function retry(int $id): void
    {
        $log = $this->repository->get($id);

        if ($log === null) {
            throw new RuntimeException(fsp__('Log not found'));
        }

        if ($log->status === self::STATUS_SUCCESS) {
            throw new RuntimeException(fsp__('Successful logs cannot be retried'));
        }

        $requestData = $log->request_data ? json_decode($log->request_data, true) : null;

        if ($requestData === null) {
            throw new RuntimeException(fsp__('Log data not found'));
        }

        $eventManager = Container::get(WorkflowDriversManager::class);
        $driver = $eventManager->get($log->driver);

        if ($driver === null) {
            throw new RuntimeException(fsp__('Log driver not found'));
        }

        $workflowEventsManager = Container::get(WorkflowEventsManager::class);

        $actionSettings = Container::get(WorkflowActionResponse::class);

        $actionSettings->setSlug($log->when);
        $actionSettings->setData($requestData['actionSettingsData']);
        $actionSettings->setWorkflowId($log->workflow_id);
        $actionSettings->setDriver($log->driver);

        $driver->handle($requestData['eventData'], $actionSettings, $workflowEventsManager->getShortcodeService());
    }
}
