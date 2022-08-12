<?php

namespace Jabe\Impl\Bpmn\Parser;

use Jabe\Impl\El\ExpressionInterface;

class FailedJobRetryConfiguration
{
    protected $retries = 0;
    protected $retryIntervals = [];
    protected $expression;

    public function __construct(?ExpressionInterface $expression = null, ?int $retries = 0, ?array $retryIntervals = [])
    {
        $this->expression = $expression;
        $this->retries = $retries;
        $this->retryIntervals = $retryIntervals;
    }

    public function getRetries(): int
    {
        return $this->retries;
    }

    public function getRetryIntervals(): array
    {
        return $this->retryIntervals;
    }

    public function getExpression(): ?ExpressionInterface
    {
        return $this->expression;
    }
}
