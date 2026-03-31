<?php

namespace FSPoster\App\Pages\Settings\Workflow\Services;

use FSPoster\App\Pages\Settings\Workflow\DTOs\Request\WorkflowRequest;
use FSPoster\App\Pages\Settings\Workflow\Mappers\WorkflowMapper;
use FSPoster\App\Pages\Settings\Workflow\Repositories\WorkflowRepository;
use FSPoster\App\Providers\Core\Container;
use ReflectionException;

class WorkflowService
{
    private WorkflowRepository $repository;

    public function __construct(WorkflowRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param WorkflowRequest $request
     * @return int
     */
    public function create(WorkflowRequest $request): int
    {
        $data = [
            'name' => $request->getName(),
            'when' => $request->getWhen(),
            'data' => $request->getData(),
            'is_active' => $request->getIsActive(),
        ];

        return $this->repository->create($data);
    }

    public function update(int $id, WorkflowRequest $request): void
    {
        $data = [];

        if ($request->getName() !== null) {
            $data['name'] = $request->getName();
        }

        if ($request->getIsActive() !== null) {
            $data['is_active'] = $request->getIsActive();
        }

        $this->repository->update($id, $data);
    }

    /**
     * @return array
     * @throws ReflectionException
     */
    public function getAll()
    {
        $mapper = Container::get(WorkflowMapper::class);

        $workflowData = $this->repository->getAll();

        $workflows = $mapper->toListResponse($workflowData);

        foreach ($workflows as $workflow) {
            $actionService = Container::get(WorkflowActionService::class);

            $workflow->setActions(array_map(function ($data) {
                return [
                    'id' => (int)$data->id,
                    'driver' => $data->driver
                ];
            }, $actionService->getActionsByWorkflowId($workflow->getId())));
        }

        return $workflows;
    }

    public function get(int $id)
    {
        return $this->repository->get($id);
    }

    public function delete(int $id): void
    {
        $actionService = Container::get(WorkflowActionService::class);

        $actionService->deleteByWorkflowId($id);
        $this->repository->delete($id);
    }

    /**
     * @return array
     */
    function getWpUsers(): array
    {
        $users = get_users();

        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'id'       => $user->ID,
                'username' => $user->user_login
            ];
        }

        return $data;
    }

    public function updateDataById(int $id, array $networks, array $channels, array $channelLabels)
    {
        $this->repository->updateDataById($id, [
            'networks' => $networks,
            'channels' => $channels,
            'channelLabels' => $channelLabels
        ]);
    }

    /**
     * @param string $eventKey
     * @return array
     */
    public function getByEventKey(string $eventKey): array
    {
        return $this->repository->getByEventKey($eventKey);
    }
}
