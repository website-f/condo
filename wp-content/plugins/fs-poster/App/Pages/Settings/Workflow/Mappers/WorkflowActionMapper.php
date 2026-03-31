<?php

namespace FSPoster\App\Pages\Settings\Workflow\Mappers;

use FSPoster\App\Pages\Settings\Workflow\DTOs\Response\WorkflowActionResponse;
use FSPoster\App\Providers\Core\Container;
use FSPoster\App\Providers\DB\Collection;

class WorkflowActionMapper
{
    public function toResponse(Collection $workflowAction): WorkflowActionResponse
    {
        $workflowActionDto = Container::get(WorkflowActionResponse::class);

        $workflowActionDto->setWorkflowId($workflowAction->workflow_id);
        $workflowActionDto->setData(json_decode($workflowAction->data, true) ?? []);
        $workflowActionDto->setSlug($workflowAction->slug);
        $workflowActionDto->setDriver($workflowAction->driver);

        return $workflowActionDto;
    }

    /**
     * @param array $workflowActions
     * @return WorkflowActionResponse[]
     */
    public function toListResponse(array $workflowActions): array
    {
        return array_map([$this, 'toResponse'], $workflowActions);
    }
}
