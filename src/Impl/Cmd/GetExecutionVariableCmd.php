<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Impl\Util\EnsureUtil;

class GetExecutionVariableCmd implements CommandInterface, \Serializable
{
    protected $executionId;
    protected $variableName;
    protected $isLocal;

    public function __construct(?string $executionId, ?string $variableName, bool $isLocal)
    {
        $this->executionId = $executionId;
        $this->variableName = $variableName;
        $this->isLocal = $isLocal;
    }

    public function serialize()
    {
        return json_encode([
            'executionId' => $this->executionId,
            'variableName' => $this->variableName,
            'isLocal' => $this->isLocal
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->executionId = $json->executionId;
        $this->variableName = $json->variableName;
        $this->isLocal = $json->isLocal;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("executionId", "executionId", $this->executionId);
        EnsureUtil::ensureNotNull("variableName", "variableName", $this->variableName);

        $execution = $commandContext
            ->getExecutionManager()
            ->findExecutionById($this->executionId);

        EnsureUtil::ensureNotNull("execution " . $this->executionId . " doesn't exist", "execution", $execution);

        $this->checkGetExecutionVariable($execution, $commandContext);

        $value = null;

        if ($this->isLocal) {
            $value = $execution->getVariableLocal($this->variableName, true);
        } else {
            $value = $execution->getVariable($this->variableName, true);
        }

        return $value;
    }

    protected function checkGetExecutionVariable(ExecutionEntity $execution, CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadProcessInstanceVariable($execution);
        }
    }
}
