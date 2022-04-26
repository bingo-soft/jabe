<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class GetHistoricJobLogExceptionStacktraceCmd implements CommandInterface
{
    protected $historicJobLogId;

    public function __construct(string $historicJobLogId)
    {
        $this->historicJobLogId = $historicJobLogId;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("historicJobLogId", "historicJobLogId", $this->historicJobLogId);

        $job = $commandContext
            ->getHistoricJobLogManager()
            ->findHistoricJobLogById($this->historicJobLogId);

        EnsureUtil::ensureNotNull("No historic job log found with id " . $this->historicJobLogId, "historicJobLog", $job);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadHistoricJobLog($job);
        }

        return $job->getExceptionStacktrace();
    }
}
