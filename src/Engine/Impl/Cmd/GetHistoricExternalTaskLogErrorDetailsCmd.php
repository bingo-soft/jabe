<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class GetHistoricExternalTaskLogErrorDetailsCmd implements CommandInterface
{
    protected $historicExternalTaskLogId;

    public function __construct(string $historicExternalTaskLogId)
    {
        $this->historicExternalTaskLogId = $historicExternalTaskLogId;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("historicExternalTaskLogId", "historicExternalTaskLogId", $this->historicExternalTaskLogId);

        $event = $commandContext
            ->getHistoricExternalTaskLogManager()
            ->findHistoricExternalTaskLogById($this->historicExternalTaskLogId);

            EnsureUtil::ensureNotNull("No historic external task log found with id " . $this->historicExternalTaskLogId, "historicExternalTaskLog", $event);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadHistoricExternalTaskLog($event);
        }

        return $event->getErrorDetails();
    }
}
