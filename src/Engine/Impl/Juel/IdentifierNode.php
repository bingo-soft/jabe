<?php

namespace BpmPlatform\Engine\Impl\Juel;

interface IdentifierNode extends Node
{
    /**
     * Get the identifier name
     */
    public function getName(): string;

    /**
     * Get the unique index of this identifier in the expression (e.g. preorder index)
     */
    public function getIndex(): int;
}
