<?php

namespace FSPoster\App\Providers\Common;

use FSPoster\App\Pages\Settings\Workflow\DTOs\Response\WorkflowActionResponse;
use FSPoster\App\Providers\Common\ShortCodeServiceForWorkflow as ShortCodeService;

interface WorkflowDriverInterface
{
    /**
     * Override this method to process event
     * @param $eventData
     * @param WorkflowActionResponse $actionSettings
     * @param ShortCodeService $shortCodeService
     * @return void
     */
    public function handle($eventData, WorkflowActionResponse $actionSettings, ShortCodeService $shortCodeService): void;
}
