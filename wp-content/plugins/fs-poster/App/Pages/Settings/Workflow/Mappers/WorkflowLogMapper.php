<?php

namespace FSPoster\App\Pages\Settings\Workflow\Mappers;

use FSPoster\App\Pages\Settings\Workflow\DTOs\Response\WorkflowLogResponse;
use FSPoster\App\Providers\Core\Container;
use FSPoster\App\Providers\DB\Collection;
use FSPoster\App\Providers\Helpers\Date;
use ReflectionException;

class WorkflowLogMapper
{
    /**
     * @param Collection $log
     * @return WorkflowLogResponse
     * @throws ReflectionException
     */
    public function toResponse(Collection $log): WorkflowLogResponse
    {
        $response = Container::get(WorkflowLogResponse::class);

        $response->setId($log->id);
        $response->setWorkflowId($log->workflow_id);
        $response->setWhen($log->when);
        $response->setDriver($log->driver);
        $response->setCreatedAt(Date::epoch($log->created_at));
        $response->setStatus($log->status);
        $response->setWorkflowName($log->workflow_name ?? fsp__('Deleted workflow'));

        if (isset($log->request_data)) {
            $requestData = json_decode($log->request_data, true);

            if (is_array($requestData)) {
                $response->setEventData($requestData['eventData'] ?? null);
                $response->setActionData($requestData['actionSettingsData'] ?? null);
                $response->setRequestData($requestData);
            }
        }

        if (isset($log->response_data)) {
            $responseData = json_decode($log->response_data, true);

            if (is_array($responseData)) {
                $response->setResponseData($responseData);
            }
        }

        if (isset($log->error_msg)) {
            $response->setErrorMsg($log->error_msg);
        }

        return $response;
    }

    /**
     * @param array $data
     * @return array<WorkflowLogResponse>
     */
    public function toListResponse(array $data): array
    {
        return array_map([$this, 'toResponse'], $data);
    }
}