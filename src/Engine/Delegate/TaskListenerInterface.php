<?php

namespace BpmPlatform\Engine\Delegate;

interface TaskListenerInterface
{
    public const EVENTNAME_CREATE = "create";
    public const EVENTNAME_ASSIGNMENT = "assignment";
    public const EVENTNAME_COMPLETE = "complete";
    public const EVENTNAME_UPDATE = "update";
    public const EVENTNAME_DELETE = "delete";
    public const EVENTNAME_TIMEOUT = "timeout";

    public function notify(DelegateTaskInterface $delegateTask): void;
}
