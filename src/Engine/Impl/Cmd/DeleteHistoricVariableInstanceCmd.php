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
    HistoricVariableInstanceEntity,
    PropertyChange
};
use BpmPlatform\Engine\Impl\Repository\ResourceDefinitionEntityInterface;
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class DeleteHistoricVariableInstanceCmd implements CommandInterface, \Serializable
{
    private $variableInstanceId;

    public function __construct(string $variableInstanceId)
    {
        $this->variableInstanceId = $variableInstanceId;
    }

    public function serialize()
    {
        return json_encode([
            'variableInstanceId' => $this->variableInstanceId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->variableInstanceId = $json->variableInstanceId;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotEmpty(BadUserRequestException::class, "variableInstanceId", $this->variableInstanceId);

        $variable = $commandContext->getHistoricVariableInstanceManager()->findHistoricVariableInstanceByVariableInstanceId($this->variableInstanceId);
        EnsureUtil::ensureNotNull(NotFoundException::class, "variable", $variable);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkDeleteHistoricVariableInstance($variable);
        }

        $commandContext
            ->getHistoricDetailManager()
            ->deleteHistoricDetailsByVariableInstanceId($this->variableInstanceId);

        $commandContext
            ->getHistoricVariableInstanceManager()
            ->deleteHistoricVariableInstanceByVariableInstanceId($this->variableInstanceId);

        // create user operation log
        $definition = null;
        try {
            if ($variable->getProcessDefinitionId() != null) {
                $definition = $commandContext->getProcessEngineConfiguration()->getDeploymentCache()->findDeployedProcessDefinitionById(
                    $variable->getProcessDefinitionId()
                );
            }/*elseif ($variable.getCaseDefinitionId() != null) {
                definition = commandContext.getProcessEngineConfiguration().getDeploymentCache().findDeployedCaseDefinitionById(variable.getCaseDefinitionId());
            }*/
        } catch (\Exception $nve) {
            // definition has been deleted already
        }
        $commandContext->getOperationLogManager()->logHistoricVariableOperation(
            UserOperationLogEntryInterface::OPERATION_TYPE_DELETE_HISTORY,
            $variable,
            $definition,
            new PropertyChange("name", null, $variable->getName())
        );

        return null;
    }
}
