<?php

namespace Jabe\Impl;

class Page
{
    protected int $firstResult = 0;
    protected int $maxResults = 0;

    public function __construct(int $firstResult, int $maxResults)
    {
        $this->firstResult = $firstResult;
        $this->maxResults = $maxResults;
    }

    public function getFirstResult(): int
    {
        return $this->firstResult;
    }

    public function getMaxResults(): int
    {
        return $this->maxResults;
    }
}
