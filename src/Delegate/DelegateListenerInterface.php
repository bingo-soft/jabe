<?php

namespace Jabe\Delegate;

interface DelegateListenerInterface
{
    public function notify(/*BaseDelegateExecutionInterface*/$instance): void;
}
