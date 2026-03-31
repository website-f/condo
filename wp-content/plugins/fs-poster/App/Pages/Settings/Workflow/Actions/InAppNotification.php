<?php

namespace FSPoster\App\Pages\Settings\Workflow\Actions;

use Exception;
use FSPoster\App\Pages\Notification\DTOs\Request\NotificationRequest;
use FSPoster\App\Pages\Notification\Registerer\NotificationWorkflowEventRegisterer;
use FSPoster\App\Pages\Notification\Services\NotificationService;
use FSPoster\App\Pages\Settings\Workflow\DTOs\Request\WorkflowLogRequest;
use FSPoster\App\Pages\Settings\Workflow\DTOs\Response\WorkflowActionResponse;
use FSPoster\App\Pages\Settings\Workflow\Services\WorkflowLogService;
use FSPoster\App\Providers\Common\ShortCodeServiceForWorkflow;
use FSPoster\App\Providers\Common\WorkflowDriver;
use FSPoster\App\Providers\Common\WorkflowDriverInterface;
use FSPoster\App\Providers\Common\WorkflowEventsManager;
use FSPoster\App\Providers\Core\Container;
use ReflectionException;

class InAppNotification extends WorkflowDriver implements WorkflowDriverInterface
{
    protected $driver = 'in_app_notification';

    public function __construct()
    {
        $this->setName(fsp__('In app notification'));
        $this->setEditAction('in_app_notification', 'in_app_notification');
    }

    /**
     * @param $eventData
     * @param WorkflowActionResponse $actionSettings
     * @param ShortCodeServiceForWorkflow $shortCodeService
     * @throws ReflectionException
     */
    public function handle($eventData, WorkflowActionResponse $actionSettings, ShortCodeServiceForWorkflow $shortCodeService): void
    {
        $actionData = $actionSettings->getData();

        if (empty($actionData)) {
            return;
        }

        $ids = [];

        $required = ['to', 'type', 'title', 'message'];

        foreach ($required as $field) {
            if (!array_key_exists($field, $actionData) || empty($actionData[$field])) {
                return;
            }
        }

        foreach ($actionData['to'] as $to) {
            $ids[] = $shortCodeService->replace($to, $eventData);
        }

        $ids = array_unique($ids);

        $type = $shortCodeService->replace($actionData['type'] ?? '', $eventData);
        $title = $shortCodeService->replace($actionData['title'] ?? '', $eventData);
        $message = $shortCodeService->replace($actionData['message'] ?? '', $eventData);

        $notificationWorkflowAction = NotificationWorkflowEventRegisterer::getEventInstance($actionSettings->getSlug());

        if ($notificationWorkflowAction === null) {
            return;
        }

        $entityId = $eventData[$notificationWorkflowAction->getEntityName()];

        if (empty($entityId)) {
            return;
        }

        $notificationWorkflowAction->setActionData($entityId);

        $workflowEventsManager = Container::get(WorkflowEventsManager::class);

        $isEnabledBackup = $workflowEventsManager->isEnabled();

        $isEnabled = !isset($actionData['run_workflows']) || $actionData['run_workflows'];
        $workflowEventsManager->setEnabled($isEnabled);

        $service = Container::get(NotificationService::class);
        $workflowLogService = Container::get(WorkflowLogService::class);
        foreach ($ids as $id) {
            if (get_user_by('ID', (int)$id) === false) {
                continue;
            }

            $request = new NotificationRequest();

            $request->setUserId($id);
            $request->setActionType($notificationWorkflowAction->getActionType());
            $request->setType($type);
            $request->setTitle($title);
            $request->setMessage($message);
            $request->setActionData($notificationWorkflowAction->getActionData());

            $hasError = false;
            $errorMsg = null;

            try {
                $notificationId = $service->createNotification($request);
            } catch (Exception $exception) {
                $hasError = true;
                $errorMsg = $exception->getMessage();
            }

            $workflowLogRequest = Container::get(WorkflowLogRequest::class);

            $actionData['to'] = [$id];

            $requestData = json_encode([
                'eventData' => $eventData,
                'actionSettingsData' => $actionData,
            ]);

            $responseData = json_encode([
                'notificationId' => isset($notificationId) ? $notificationId : null,
            ]);

            $workflowLogRequest->setWorkflowId($actionSettings->getWorkflowId());
            $workflowLogRequest->setRequestData($requestData);
            $workflowLogRequest->setResponseData($responseData);
            $workflowLogRequest->setWhen($actionSettings->getSlug());
            $workflowLogRequest->setDriver($this->driver);
            $workflowLogRequest->setStatus($hasError ? WorkflowLogService::STATUS_ERROR : WorkflowLogService::STATUS_SUCCESS);
            $workflowLogRequest->setErrorMsg($errorMsg);

            $workflowLogService->create($workflowLogRequest);
        }

        $workflowEventsManager->setEnabled($isEnabledBackup);
    }
}
