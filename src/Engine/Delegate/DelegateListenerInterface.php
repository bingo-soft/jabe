<?php

namespace Jabe\Engine\Delegate;

interface DelegateListenerInterface
{
    public function notify(BaseDelegateExecutionInterface $instance): void;
}
