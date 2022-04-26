<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\BadUserRequestException;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class SignalCmd implements CommandInterface, \Serializable
{
    protected $executionId;
    protected $signalName;
    protected $signalData;
    protected $processVariables = [];

    public function __construct(?string $executionId, string $signalName, $signalData, array $processVariables)
    {
        $this->executionId = $executionId;
        $this->signalName = $signalName;
        $this->signalData = $signalData;
        $this->processVariables = $processVariables;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("executionId is null", "executionId", $this->executionId);

        $execution = $commandContext
                ->getExecutionManager()
                ->findExecutionById($this->executionId);
        EnsureUtil::ensureNotNull("execution " . $this->executionId . " doesn't exist", "execution", $execution);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkUpdateProcessInstance($execution);
        }

        if (!empty($this->processVariables)) {
            $execution->setVariables($this->processVariables);
        }

        $execution->signal($this->signalName, $this->signalData);
        return null;
    }
}
