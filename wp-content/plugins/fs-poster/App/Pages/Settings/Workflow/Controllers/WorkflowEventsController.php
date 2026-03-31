<?php

namespace FSPoster\App\Pages\Settings\Workflow\Controllers;

use FSPoster\App\Pages\Settings\Workflow\Services\WorkflowService;
use FSPoster\App\Providers\Core\RestRequest;

class WorkflowEventsController
{
    private WorkflowService $service;

    public function __construct(WorkflowService $service)
    {
        $this->service = $service;
    }

    public function scheduleFailed(RestRequest $request): array
    {
        $id = $request->param('workflow_id',null, RestRequest::TYPE_INTEGER);
        $networks = $request->param('networks' , [], RestRequest::TYPE_ARRAY);
        $channels = $request->param('channels' , [], RestRequest::TYPE_ARRAY);
        $channelLabels = $request->param('channelLabels' , [], RestRequest::TYPE_ARRAY);

        if ($id === null) {
            throw new \RuntimeException(fsp__('Workflow id is required.'));
        }

        $this->service->updateDataById($id, $networks, $channels, $channelLabels);

        return [];
    }
}