<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class SignalCmd implements CommandInterface
{
    protected $executionId;
    protected $signalName;
    protected $signalData;
    protected $processVariables = [];

    public function __construct(?string $executionId, ?string $signalName, $signalData, array $processVariables)
    {
        $this->executionId = $executionId;
        $this->signalName = $signalName;
        $this->signalData = $signalData;
        $this->processVariables = $processVariables;
    }

    public function __serialize(): array
    {
        return [
            'executionId' => $this->executionId,
            'signalName' => $this->signalName,
            'signalData' => $this->signalData,
            'processVariables' => $this->processVariables
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->executionId = $data['executionId'];
        $this->signalName = $data['signalName'];
        $this->signalData = $data['signalData'];
        $this->processVariables = $data['processVariables'];
    }

    public function execute(CommandContext $commandContext, ...$args)
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

    public function isRetryable(): bool
    {
        return false;
    }
}
