<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\BadUserRequestException;
use BpmPlatform\Engine\Exception\{
    NotFoundException,
    NullValueException
};
use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    HistoricProcessInstanceEntity,
    PropertyChange
};
use BpmPlatform\Engine\Impl\Repository\ResourceDefinitionEntityInterface;
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class DeleteHistoricVariableInstancesByProcessInstanceIdCmd implements CommandInterface, \Serializable
{
    private $processInstanceId;

    public function __construct(string $processInstanceId)
    {
        $this->processInstanceId = $processInstanceId;
    }

    public function serialize()
    {
        return json_encode([
            'processInstanceId' => $this->processInstanceId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->processInstanceId = $json->processInstanceId;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotEmpty(BadUserRequestException::class, "processInstanceId", $this->processInstanceId);

        $instance = $commandContext->getHistoricProcessInstanceManager()->findHistoricProcessInstance($this->processInstanceId);
        EnsureUtil::ensureNotNull(NotFoundException::class, "instance", $instance);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkDeleteHistoricVariableInstancesByProcessInstance($instance);
        }

        $commandContext->getHistoricDetailManager()->deleteHistoricDetailsByProcessInstanceIds($this->processInstanceId);
        $commandContext->getHistoricVariableInstanceManager()->deleteHistoricVariableInstanceByProcessInstanceIds($this->processInstanceId);

        // create user operation log
        $definition = null;
        try {
            $definition = $commandContext->getProcessEngineConfiguration()->getDeploymentCache()->findDeployedProcessDefinitionById(
                $instance->getProcessDefinitionId()
            );
        } catch (\Exception $nve) {
            // definition has been deleted already
        }
        $commandContext->getOperationLogManager()->logHistoricVariableOperation(
            UserOperationLogEntryInterface::OPERATION_TYPE_DELETE_HISTORY,
            $instance,
            $definition,
            PropertyChange::emptyChange()
        );

        return null;
    }
}
