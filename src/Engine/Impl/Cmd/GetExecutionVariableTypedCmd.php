<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Engine\Impl\Util\EnsureUtil;

class GetExecutionVariableTypedCmd implements CommandInterface, \Serializable
{
    protected $executionId;
    protected $variableName;
    protected $isLocal;
    protected $deserializeValue;

    public function __construct(string $executionId, string $variableName, bool $isLocal, bool $deserializeValue)
    {
        $this->executionId = $executionId;
        $this->variableName = $variableName;
        $this->isLocal = $isLocal;
        $this->deserializeValue = $deserializeValue;
    }

    public function serialize()
    {
        return json_encode([
            'executionId' => $this->executionId,
            'variableName' => $this->variableName,
            'isLocal' => $this->isLocal,
            'deserializeValue' => $this->deserializeValue
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->executionId = $json->executionId;
        $this->variableName = $json->variableName;
        $this->isLocal = $json->isLocal;
        $this->deserializeValue = $json->deserializeValue;
    }

    public function execute(CommandContext $commandContext)
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
}
