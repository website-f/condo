<?php

namespace FSPoster\App\Pages\Settings\Workflow\Services;


use FSPoster\App\Models\WorkflowAction;
use FSPoster\App\Pages\Settings\Workflow\DTOs\Request\WorkFlowActionRequest;
use FSPoster\App\Pages\Settings\Workflow\DTOs\Response\WorkflowActionResponse;
use FSPoster\App\Pages\Settings\Workflow\Mappers\WorkflowActionMapper;
use FSPoster\App\Pages\Settings\Workflow\Repositories\WorkflowActionRepository;
use FSPoster\App\Providers\DB\Collection;

class WorkflowActionService
{
    private WorkflowActionRepository $repository;
    private WorkflowActionMapper $mapper;

    public function __construct(WorkflowActionRepository $repository, WorkflowActionMapper $mapper)
    {
        $this->repository = $repository;
        $this->mapper = $mapper;
    }

    /**
     * @param WorkFlowActionRequest $request
     * @return int
     */
    public function create(WorkFlowActionRequest $request): int
    {
        $data = [
            'workflow_id' => $request->getWorkflowId(),
            'driver' => $request->getDriver(),
            'data' => $request->getData(),
            'is_active' => $request->getIsActive(),
        ];

        return $this->repository->create($data);
    }

    public function getActionsByWorkflowId(int $workflowId): array
    {
        return $this->repository->getActionsByWorkflowId($workflowId);
    }

    public function deleteByWorkflowId(int $workflowId): void
    {
        $this->repository->deleteByWorkflowId($workflowId);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }

    /**
     * @param int $id
     * @return WorkflowAction|Collection|null
     */
    public function get(int $id)
    {
        return $this->repository->get($id);
    }

    public function update(int $id, array $data): void
    {
        $this->repository->update($id, $data);
    }

    /**
     * @param int $workflowId
     * @return WorkflowActionResponse[]
     */
    public function getActiveActionsByWorkflowId(int $workflowId): array
    {
        $workflowActions = $this->repository->getActiveActionsByWorkflowId($workflowId);

        return $this->mapper->toListResponse($workflowActions);
    }
}
