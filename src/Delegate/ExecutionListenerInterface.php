<?php

namespace Jabe\Delegate;

interface ExecutionListenerInterface extends DelegateListenerInterface
{
    public const EVENTNAME_START = "start";
    public const EVENTNAME_END = "end";
    public const EVENTNAME_TAKE = "take";

    public function notify(/*DelegateExecutionInterface*/$execution): void;
}
