<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Impl\Util\EnsureUtil;

class GetExecutionVariableTypedCmd implements CommandInterface
{
    protected $executionId;
    protected $variableName;
    protected $isLocal;
    protected $deserializeValue;

    public function __construct(?string $executionId, ?string $variableName, bool $isLocal, bool $deserializeValue)
    {
        $this->executionId = $executionId;
        $this->variableName = $variableName;
        $this->isLocal = $isLocal;
        $this->deserializeValue = $deserializeValue;
    }

    public function __serialize(): array
    {
        return [
            'executionId' => $this->executionId,
            'variableName' => $this->variableName,
            'isLocal' => $this->isLocal,
            'deserializeValue' => $this->deserializeValue
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->executionId = $data['executionId'];
        $this->variableName = $data['variableName'];
        $this->isLocal = $data['isLocal'];
        $this->deserializeValue = $data['deserializeValue'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureNotNull("executionId", "executionId", $this->executionId);
        EnsureUtil::ensureNotNull("variableName", "variableName", $this->variableName);

        $execution = $commandContext
            ->getExecutionManager()
            ->findExecutionById($this->executionId);

            EnsureUtil::ensureNotNull("execution " . $this->executionId . " doesn't exist", "execution", $execution);

        $this->checkGetExecutionVariableTyped($execution, $commandContext);

        $value = null;

        if ($this->isLocal) {
            $value = $execution->getVariableLocalTyped($this->variableName, $this->deserializeValue);
        } else {
            $value = $execution->getVariableTyped($this->variableName, $this->deserializeValue);
        }

        return $value;
    }

    public function checkGetExecutionVariableTyped(ExecutionEntity $execution, CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadProcessInstanceVariable($execution);
        }
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
