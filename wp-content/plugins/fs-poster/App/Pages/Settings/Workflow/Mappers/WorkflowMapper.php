<?php

namespace FSPoster\App\Pages\Settings\Workflow\Mappers;

use FSPoster\App\Pages\Settings\Workflow\DTOs\Response\WorkflowResponse;
use FSPoster\App\Providers\DB\Collection;

class WorkflowMapper
{
    /**
     * @param Collection $workflow
     * @return WorkflowResponse
     */
    public function toResponse(Collection $workflow): WorkflowResponse
    {
        $response = new WorkflowResponse();
        $response->setId($workflow->id);
        $response->setName($workflow->name);
        $response->setWhen($workflow->when);
        $response->setIsActive($workflow->is_active);
        $response->setData($workflow->data ?? '');

        return $response;
    }

    /**
     * @param array $data
     * @return array
     */
    public function toListResponse(array $data): array
    {
        return array_map([$this, 'toResponse'], $data);
    }
}