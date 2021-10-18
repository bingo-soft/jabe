<?php

namespace BpmPlatform\Engine\Delegate;

interface PhpDelegateInterface
{
    public function execute(DelegateExecutionInterface $execution);
}
