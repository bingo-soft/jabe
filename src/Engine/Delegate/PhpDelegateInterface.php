<?php

namespace Jabe\Engine\Delegate;

interface PhpDelegateInterface
{
    public function execute(DelegateExecutionInterface $execution);
}
