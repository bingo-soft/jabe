<?php

namespace BpmPlatform\Engine\Impl\Tree;

class ExecutionVisitorWrapper implements TreeVisitorInterface
{
    private $collector;

    public function __construct(TreeVisitorInterface $collector)
    {
        $this->collector = $collector;
    }

    public function visit(/*ActivityExecutionTuple */$tuple): void
    {
        $this->collector->visit($tuple->getExecution());
    }
}
