<?php

namespace BpmPlatform\Engine\Impl\Language;

interface TreeCache
{
    /**
     * Lookup tree
     */
    public function get(string $expression): ?Tree;

    /**
     * Cache tree
     */
    public function put(string $expression, Tree $tree): void;
}
