<?php

namespace Jabe\Delegate;

interface CaseExecutionListenerInterface extends DelegateListenerInterface
{
    public const CREATE = "create";
    public const ENABLE = "enable";
    public const DISABLE = "disable";
    public const RE_ENABLE = "reenable";
    public const START = "start";
    public const MANUAL_START = "manualStart";
    public const COMPLETE = "complete";
    public const RE_ACTIVATE = "reactivate";
    public const TERMINATE = "terminate";
    public const EXIT = "exit";
    public const PARENT_TERMINATE = "parentTerminate";
    public const SUSPEND = "suspend";
    public const RESUME = "resume";
    public const PARENT_SUSPEND = "parentSuspend";
    public const PARENT_RESUME = "parentResume";
    public const CLOSE = "close";
    public const OCCUR = "occur";

    public function notify(DelegateCaseExecutionInterface $caseExecution): void;
}
