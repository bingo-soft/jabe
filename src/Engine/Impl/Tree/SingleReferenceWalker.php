<?php

namespace BpmPlatform\Engine\Impl\Tree;

abstract class SingleReferenceWalker extends ReferenceWalker
{
    public function __construct($initialElement)
    {
        parent::__construct($initialElement);
    }

    protected function nextElements(): array
    {
        $nextElement = $this->nextElement();

        if ($nextElement != null) {
            return [$nextElement];
        }
        return [];
    }

    abstract protected function nextElement();
}
