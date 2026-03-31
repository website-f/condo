<?php

namespace FSPoster\App\Pages\Settings\Workflow\Repositories;

use FSPoster\App\Models\Workflow;
use FSPoster\App\Models\WorkflowLog;
use FSPoster\App\Providers\DB\Collection;

/**
 * @property int $id
 * @property int $workflow_id
 * @property string $when
 * @property string $driver
 * @property string $data
 * @property string $error_msg
 * @property string $status
 */
class WorkflowLogRepository
{
    /**
     * @param int $page
     * @return array
     */
    public function getAll(int $page): array
    {
        return WorkflowLog::query()->leftJoin(Workflow::getTableName(), [], WorkflowLog::getField('workflow_id'), Workflow::getField('id'))
             ->select([
                 WorkflowLog::getField('id'),
                 WorkflowLog::getField('workflow_id'),
                 WorkflowLog::getField('when'),
                 WorkflowLog::getField('driver'),
                 WorkflowLog::getField('created_at'),
                 WorkflowLog::getField('status'),
                 WorkflowLog::getField('error_msg'),
                 Workflow::getField('name') . ' as workflow_name',
             ])
             ->orderBy(WorkflowLog::getField('id') . ' DESC')
             ->offset(($page-1) * 10)->limit(10)->fetchAll();
    }

    /**
     * @param int $id
     * @return WorkflowLog|Collection|null
     */
    public function get(int $id)
    {
        return WorkflowLog::query()->leftJoin(Workflow::getTableName(), [], WorkflowLog::getField('workflow_id'), Workflow::getField('id'))
            ->select([
                WorkflowLog::getField('id'),
                WorkflowLog::getField('workflow_id'),
                WorkflowLog::getField('when'),
                WorkflowLog::getField('driver'),
                WorkflowLog::getField('created_at'),
                WorkflowLog::getField('status'),
                WorkflowLog::getField('request_data'),
                WorkflowLog::getField('response_data'),
                WorkflowLog::getField('error_msg'),
                Workflow::getField('name') . ' as workflow_name',
            ])->where(WorkflowLog::getField('id'), $id)->fetch();
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        WorkflowLog::query()->delete();
    }

    /**
     * @return int
     */
    public function getLogsCount(): int
    {
        return WorkflowLog::query()->count();
    }

    public function create(array $data): void
    {
        WorkflowLog::query()->insert($data);
    }

    public function delete(int $id): void
    {
        WorkflowLog::query()->where('id', $id)->delete();
    }

    public function update(int $id, array $data): void
    {
        WorkflowLog::query()->where('id', $id)->update($data);
    }
}
