<?php

namespace BpmPlatform\Engine\Impl\Pvm\Delegate;

use BpmPlatform\Engine\Delegate\DelegateTaskInterface;

interface TaskListenerInterface
{
    public const EVENTNAME_CREATE = "create";
    public const EVENTNAME_ASSIGNMENT = "assignment";
    public const EVENTNAME_COMPLETE = "complete";

    public function notify(DelegateTaskInterface $delegateTask): void;
}
