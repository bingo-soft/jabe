<?php

namespace Jabe\Engine\Impl\Juel;

interface Node
{
    /**
     * Get the node's number of children.
     */
    public function getCardinality(): int;

    /**
     * Get i'th child
     */
    public function getChild(int $i): ?Node;
}
