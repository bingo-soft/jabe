<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Entity\JobEntity;

class DeleteJobsCmd implements CommandInterface
{
    protected $jobIds = [];
    protected $cascade;

    public function __construct($jobIds, ?bool $cascade = false)
    {
        if (is_string($jobIds)) {
            $this->jobIds[] = $jobIds;
        } elseif (is_array($jobIds)) {
            $this->jobIds = $jobIds;
        }
        $this->cascade = $cascade;
    }

    public function execute(CommandContext $commandContext)
    {
        $jobToDelete = null;
        foreach ($this->jobIds as $jobId) {
            $jobToDelete = Context::getCommandContext()
            ->getJobManager()
            ->findJobById($jobId);

            if ($jobToDelete != null) {
                // When given job doesn't exist, ignore
                $jobToDelete->delete();

                if ($this->cascade) {
                    $commandContext
                    ->getHistoricJobLogManager()
                    ->deleteHistoricJobLogByJobId($this->jobId);
                }
            }
        }
        return null;
    }
}
