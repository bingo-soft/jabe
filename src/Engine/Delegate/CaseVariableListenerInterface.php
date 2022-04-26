<?php

namespace Jabe\Engine\Delegate;

interface CaseVariableListenerInterface extends VariableListenerInterface
{
    public const CREATE = VariableListenerInterface::CREATE;
    public const UPDATE = VariableListenerInterface::UPDATE;
    public const DELETE = VariableListenerInterface::DELETE;

    public function notify(DelegateCaseVariableInstanceInterface $variableInstance): void;
}
