<?php

namespace BpmPlatform\Engine\Impl\Language;

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
