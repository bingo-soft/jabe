<?php

namespace BpmPlatform\Engine\Delegate;

interface DelegateListenerInterface
{
    public function notify(BaseDelegateExecutionInterface $instance): void;
}
