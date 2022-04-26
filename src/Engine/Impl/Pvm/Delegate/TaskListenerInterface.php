<?php

namespace Jabe\Engine\Impl\Pvm\Delegate;

use Jabe\Engine\Delegate\DelegateTaskInterface;

interface TaskListenerInterface
{
    public const EVENTNAME_CREATE = "create";
    public const EVENTNAME_ASSIGNMENT = "assignment";
    public const EVENTNAME_COMPLETE = "complete";

    public function notify(DelegateTaskInterface $delegateTask): void;
}
