<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Impl\Util\EnsureUtil;
use Jabe\Variable\Impl\VariableMapImpl;

class GetExecutionVariablesCmd implements CommandInterface, \Serializable
{
    protected $executionId;
    protected $variableNames;
    protected $isLocal;
    protected $deserializeValues;

    public function __construct(?string $executionId, array $variableNames, bool $isLocal, bool $deserializeValues)
    {
        $this->executionId = $executionId;
        $this->variableNames = $variableNames;
        $this->isLocal = $isLocal;
        $this->deserializeValues = $deserializeValues;
    }

    public function serialize()
    {
        return json_encode([
            'executionId' => $this->executionId,
            'variableNames' => $this->variableNames,
            'isLocal' => $this->isLocal,
            'deserializeValue' => $this->deserializeValue
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->executionId = $json->executionId;
        $this->variableNames = $json->variableNames;
        $this->isLocal = $json->isLocal;
        $this->deserializeValue = $json->deserializeValue;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureNotNull("executionId", "executionId", $this->executionId);

        $execution = $commandContext
            ->getExecutionManager()
            ->findExecutionById($this->executionId);

        EnsureUtil::ensureNotNull("execution " . $this->executionId . " doesn't exist", "execution", $execution);

        $this->checkGetExecutionVariables($execution, $commandContext);

        $executionVariables = new VariableMapImpl();

        // collect variables from execution
        $execution->collectVariables($executionVariables, $this->variableNames, $this->isLocal, $this->deserializeValues);

        return $executionVariables;
    }

    protected function checkGetExecutionVariables(ExecutionEntity $execution, CommandContext $commandContext): void
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
