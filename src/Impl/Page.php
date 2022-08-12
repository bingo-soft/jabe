<?php

namespace Jabe\Impl;

class Page
{
    protected $firstResult;
    protected $maxResults;

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
