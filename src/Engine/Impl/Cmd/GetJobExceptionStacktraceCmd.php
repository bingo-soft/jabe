<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class GetJobExceptionStacktraceCmd implements CommandInterface, \Serializable
{
    private $jobId;

    public function __construct(string $jobId)
    {
        $this->jobId = $jobId;
    }

    public function serialize()
    {
        return json_encode([
            'jobId' => $this->jobId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->jobId = $json->jobId;
    }

    public function execute(CommandContext $commandContext)
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
}
