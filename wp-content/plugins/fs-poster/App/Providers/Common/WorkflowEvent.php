<?php

namespace FSPoster\App\Providers\Common;

class WorkflowEvent
{
    private $key;
    private $title;

    private $editAction;
    private $availableParams;

    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    public function setTitle($title): WorkflowEvent
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $route
     * @param $action
     * @return $this
     */
    public function setEditAction($route, $action): WorkflowEvent
    {
        $this->editAction = $route . '.' . $action;

        return $this;
    }

    public function getEditAction()
    {
        return $this->editAction;
    }

    /**
     * @param $params
     * @return $this
     */
    public function setAvailableParams($params): WorkflowEvent
    {
        $this->availableParams = $params;

        return $this;
    }

    public function getAvailableParams()
    {
        return $this->availableParams;
    }
}
