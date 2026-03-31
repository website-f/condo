<?php

namespace FSPoster\App\Pages\Settings\Workflow\Repositories;

use FSPoster\App\Models\Workflow;
use FSPoster\App\Models\WorkflowAction;
use FSPoster\App\Providers\DB\Collection;

class WorkflowRepository
{
    public function create(array $data): int
    {
        Workflow::query()->insert($data);

        return Workflow::lastId();
    }

    public function update(int $id, array $data): void
    {
        Workflow::where('id', $id)->update($data);
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return Workflow::orderBy('id DESC')->fetchAll();
    }

    /**
     * @param int $id
     * @return Workflow|Collection
     */
    public function get(int $id)
    {
        return Workflow::where('id', $id)->fetch();
    }

    public function updateDataById(int $id, array $data): void
    {
        Workflow::where('id', $id)
            ->update([
                'data' => json_encode($data)
            ]);
    }

    public function delete(int $id)
    {
        Workflow::where('id', $id)->delete();
    }

    public function getByEventKey(string $eventKey): array
    {
        return Workflow::query()
            ->where('`when`', $eventKey)
            ->where('is_active', true)
            ->fetchAll();
    }
}
