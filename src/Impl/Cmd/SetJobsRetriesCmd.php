<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class SetJobsRetriesCmd extends AbstractSetJobRetriesCmd implements CommandInterface
{
    protected $jobIds;
    protected $retries;

    public function __construct(array $jobIds, int $retries)
    {
        EnsureUtil::ensureNotEmpty("Job ID's", "jobIds", $jobIds);
        EnsureUtil::ensureGreaterThanOrEqual("The number of retries cannot be negative", "Retries count", $retries, 0);

        $this->jobIds = $jobIds;
        $this->retries = $retries;
    }

    public function __serialize(): array
    {
        return [
            'jobIds' => $this->jobIds,
            'retries' => $this->retries
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->jobIds = $data['jobIds'];
        $this->retries = $data['retries'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        foreach ($this->jobIds as $id) {
            $this->setJobRetriesByJobId($id, $this->retries, $commandContext);
        }
        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
