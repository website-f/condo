<?php

namespace FSPoster\App\Pages\Settings\Workflow\Controllers;

use Exception;
use FSPoster\App\Pages\Settings\Workflow\DTOs\Request\WorkFlowActionRequest;
use FSPoster\App\Pages\Settings\Workflow\Services\WorkflowActionService;
use FSPoster\App\Pages\Settings\Workflow\Services\WorkflowService;
use FSPoster\App\Providers\Common\WorkflowDriversManager;
use FSPoster\App\Providers\Common\WorkflowEventsManager;
use FSPoster\App\Providers\Core\RestRequest;
use RuntimeException;

class WorkflowActionController
{
    private WorkflowDriversManager $workflowDriversManager;
    private WorkflowEventsManager $workflowEventsManager;
    private WorkflowActionService $actionService;
    private WorkflowService $workflowService;

    public function __construct(WorkflowEventsManager $workflowEventsManager, WorkflowActionService $actionService, WorkflowService $workflowService)
    {
        $this->workflowEventsManager = $workflowEventsManager;
        $this->workflowDriversManager = $this->workflowEventsManager->getDriverManager();
        $this->actionService = $actionService;
        $this->workflowService = $workflowService;
    }

    /**
     * @return array{action_id: int}
     * @throws Exception
     */
    public function add(RestRequest $request): array
    {
        $id = $request->require('workflow_id', RestRequest::TYPE_INTEGER, fsp__('Workflow id is required'));
        $driver = $request->require('action_driver', RestRequest::TYPE_STRING, fsp__('Action driver is required'));

        $workflow = $this->workflowService->get($id);

        if ($workflow === null) {
            throw new \RuntimeException(fsp__('Workflow not found.'));
        }

        $workflowDriver = $this->workflowDriversManager->get($driver);

        if ($workflowDriver === null) {
            throw new \RuntimeException(fsp__('Workflow driver not found.'));
        }

        $workflowActionRequest = new WorkFlowActionRequest();

        $workflowActionRequest->setWorkflowId($id);
        $workflowActionRequest->setDriver($driver);
        $workflowActionRequest->setIsActive(1);

        $actionId = $this->actionService->create($workflowActionRequest);

        return [
            'action_id' => $actionId
        ];
    }

    public function inAppNotificationView(RestRequest $request): array
    {
        $id = $request->param('id',null, RestRequest::TYPE_INTEGER);

        if ($id === null) {
            throw new \RuntimeException(fsp__('Workflow action id is required.'));
        }

        $workflowActionInfo = $this->actionService->get($id);

        if (! $workflowActionInfo) {
            throw new \RuntimeException(fsp__('Workflow action not found.'));
        }

        $data = json_decode($workflowActionInfo->data, true);

        $workflow = $this->workflowService->get($workflowActionInfo->workflow_id);

        if ($workflow === null) {
            throw new \RuntimeException(fsp__('Workflow not found.'));
        }

        $availableParams = $this->workflowEventsManager->get($workflow->when)->getAvailableParams();

        $subjectAndBodyShortcodes = $this->workflowEventsManager->getShortcodeService()->getShortCodesList($availableParams);

        return  [
            'to' => $data['to'] ?? [],
            'title' => $data['title'] ?? '',
            'message' => $data['message'] ?? '',
            'type' => $data['type'] ?? '',
            'shortCodes' => $subjectAndBodyShortcodes
        ];
    }

    public function inAppNotificationEdit(RestRequest $request): array
    {
        $id = $request->param('id', 0, RestRequest::TYPE_INTEGER);
        $is_active = $request->param('is_active', 1, RestRequest::TYPE_INTEGER);
        $to = $request->param('to', [], RestRequest::TYPE_ARRAY);
        $title = $request->param('title', '', RestRequest::TYPE_STRING);
        $message = $request->param('message', '', RestRequest::TYPE_STRING);
        $type = $request->param('type', '', RestRequest::TYPE_STRING);
        $run_workflows = $request->param('run_workflows', 1, RestRequest::TYPE_INTEGER);

        $checkWorkflowActionExist = $this->actionService->get($id);

        if (! $checkWorkflowActionExist) {
            throw new \RuntimeException(fsp__('Workflow action not found.'));
        }

        $newData = [
            'to' => $to,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'run_workflows' => $run_workflows == 1
        ];

        $actionData = [
            'data' => json_encode($newData),
            'is_active' => $is_active
        ];

        $this->actionService->update($id, $actionData);

        return [];
    }

    public function delete(RestRequest $request): array
    {
        $id = $request->param('id', null, RestRequest::TYPE_INTEGER);

        if ($id === null) {
            throw new RuntimeException(fsp__('Workflow id is required.'));
        }

        $workflowActionInfo = $this->actionService->get($id);

        if ($workflowActionInfo === null) {
            throw new \RuntimeException(fsp__('Workflow action not found.'));
        }

        $this->actionService->delete($id);

        return [];
    }
}