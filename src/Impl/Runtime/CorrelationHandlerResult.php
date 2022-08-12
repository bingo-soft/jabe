<?php

namespace Jabe\Impl\Runtime;

use Jabe\Impl\Persistence\Entity\{
    ExecutionEntity,
    ProcessDefinitionEntity
};
use Jabe\Repository\ProcessDefinitionInterface;
use Jabe\Runtime\{
    ExecutionInterface,
    MessageCorrelationResultType
};

class CorrelationHandlerResult
{
    /**
     * @see MessageCorrelationResultType#Execution
     * @see MessageCorrelationResultType#ProcessDefinition
     */
    protected $resultType;

    protected $executionEntity;
    protected $processDefinitionEntity;
    protected $startEventActivityId;

    public static function matchedExecution(ExecutionEntity $executionEntity): CorrelationHandlerResult
    {
        $messageCorrelationResult = new CorrelationHandlerResult();
        $messageCorrelationResult->resultType = MessageCorrelationResultType::EXECUTION;
        $messageCorrelationResult->executionEntity = $executionEntity;
        return $messageCorrelationResult;
    }

    public static function matchedProcessDefinition(ProcessDefinitionEntity $processDefinitionEntity, string $startEventActivityId): CorrelationHandlerResult
    {
        $messageCorrelationResult = new CorrelationHandlerResult();
        $messageCorrelationResult->processDefinitionEntity = $processDefinitionEntity;
        $messageCorrelationResult->startEventActivityId = $startEventActivityId;
        $messageCorrelationResult->resultType = MessageCorrelationResultType::PROCESS_DEFINITION;
        return $messageCorrelationResult;
    }

    // getters ////////////////////////////////////////////

    public function getExecutionEntity(): ?ExecutionEntity
    {
        return $this->executionEntity;
    }

    public function getProcessDefinitionEntity(): ProcessDefinitionEntity
    {
        return $this->processDefinitionEntity;
    }

    public function getStartEventActivityId(): string
    {
        return $this->startEventActivityId;
    }

    public function getResultType(): string
    {
        return $this->resultType;
    }

    public function getExecution(): ?ExecutionInterface
    {
        return $this->executionEntity;
    }

    public function getProcessDefinition(): ProcessDefinitionInterface
    {
        return $this->processDefinitionEntity;
    }
}
