<?php

namespace BpmPlatform\Engine\Impl\JobExecutor;

interface JobAcquisitionStrategyInterface
{
    public function reconfigure(JobAcquisitionContext $context): void;

    public function getWaitTime(): int;

    public function getNumJobsToAcquire(string $processEngine): int;
}
