<?php

namespace Jabe\Application;

use Jabe\Delegate\BaseDelegateExecutionInterface;

class InvocationContext
{
    protected $execution;

    public function __construct(BaseDelegateExecutionInterface $execution)
    {
        $this->execution = $execution;
    }

    public function getExecution(): ?BaseDelegateExecutionInterface
    {
        return $this->execution;
    }

    public function __toString()
    {
        return "InvocationContext [execution=" . $this->execution . "]";
    }
}
