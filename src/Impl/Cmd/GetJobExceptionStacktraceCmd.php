<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class GetJobExceptionStacktraceCmd implements CommandInterface
{
    private $jobId;

    public function __construct(?string $jobId)
    {
        $this->jobId = $jobId;
    }

    public function __serialize(): array
    {
        return [
            'jobId' => $this->jobId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->jobId = $data['jobId'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureNotNull("jobId", "jobId", $this->jobId);

        $job = $commandContext
            ->getJobManager()
            ->findJobById($this->jobId);

            EnsureUtil::ensureNotNull("No job found with id " . $this->jobId, "job", $job);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadJob($job);
        }

        return $job->getExceptionStacktrace();
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
