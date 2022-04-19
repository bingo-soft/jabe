<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class SetJobsRetriesCmd extends AbstractSetJobRetriesCmd implements CommandInterface, \Serializable
{
    protected $jobIds;
    protected $retries;

    public function __construct(array $jobIds, int $retries)
    {
        EnsureUtil::ensureNotEmpty("Job ID's", "jobIds", $jobIds);
        EnsureUtil::ensureGreaterThanOrEqual("Retries count", $retries, 0);

        $this->jobIds = $jobIds;
        $this->retries = $retries;
    }

    public function serialize()
    {
        return json_encode([
            'jobIds' => $this->jobIds,
            'retries' => $this->retries
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->jobIds = $json->jobIds;
        $this->retries = $json->retries;
    }

    public function execute(CommandContext $commandContext)
    {
        foreach ($this->jobIds as $id) {
            $this->setJobRetriesByJobId($id, $this->retries, $commandContext);
        }
        return null;
    }
}
