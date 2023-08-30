<?php

namespace Jabe\Delegate;

interface VariableListenerInterface
{
    public const CREATE = "create";
    public const UPDATE = "update";
    public const DELETE = "delete";

    public function notify(DelegateVariableInstanceInterface $variableInstance): void;
}
