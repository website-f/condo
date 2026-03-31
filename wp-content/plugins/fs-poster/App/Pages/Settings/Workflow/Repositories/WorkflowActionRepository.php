<?php

namespace FSPoster\App\Pages\Settings\Workflow\Repositories;


use FSPoster\App\Models\Workflow;
use FSPoster\App\Models\WorkflowAction;
use FSPoster\App\Providers\DB\Collection;

class WorkflowActionRepository
{
    /**
     * @param array $data
     * @return int
     */
    public function create(array $data): int
    {
        WorkflowAction::query()->insert($data);

        return WorkflowAction::lastId();
    }

    public function getActionsByWorkflowId(int $workflowId): array
    {
        return WorkflowAction::query()->where('workflow_id', $workflowId)->fetchAll();
    }

    public function deleteByWorkflowId(int $workflowId): void
    {
        WorkflowAction::query()->where('workflow_id', $workflowId)->delete();
    }

    /**
     * @param int $id
     * @return void
     */
    public function delete(int $id): void
    {
        WorkflowAction::query()->where('id', $id)->delete();
    }

    /**
     * @param int $id
     * @return WorkflowAction|Collection|null
     */
    public function get(int $id)
    {
        return WorkflowAction::query()->where('id', $id)->fetch();
    }

    public function update(int $id, array $data): void
    {
        WorkflowAction::query()->where('id', $id)->update($data);
    }

    public function getActiveActionsByWorkflowId(int $workflowId): array
    {
        return WorkflowAction::query()->leftJoin('workflow')
            ->where(WorkflowAction::getField('workflow_id'), $workflowId)
            ->where(WorkflowAction::getField('is_active'), 1)
            ->select([
                WorkflowAction::getField('id'),
                WorkflowAction::getField('workflow_id'),
                WorkflowAction::getField('driver'),
                WorkflowAction::getField('data'),
                Workflow::getField('when') . ' as slug'
            ])
            ->fetchAll();
    }
}
