<?php

namespace Jabe\Impl\Tree;

class ScopeVisitorWrapper implements TreeVisitorInterface
{
    private $collector;

    public function __construct(TreeVisitorInterface $collector)
    {
        $this->collector = $collector;
    }

    public function visit(/*ActivityExecutionTuple */$tuple): void
    {
        $this->collector->visit($tuple->getScope());
    }
}
