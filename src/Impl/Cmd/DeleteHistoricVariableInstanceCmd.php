<?php

namespace Jabe\Impl\Cmd;

use Jabe\BadUserRequestException;
use Jabe\Exception\{
    NotFoundException,
    NullValueException
};
use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    HistoricVariableInstanceEntity,
    PropertyChange
};
use Jabe\Impl\Util\EnsureUtil;

class DeleteHistoricVariableInstanceCmd implements CommandInterface
{
    private $variableInstanceId;

    public function __construct(?string $variableInstanceId)
    {
        $this->variableInstanceId = $variableInstanceId;
    }

    public function __serialize(): array
    {
        return [
            'variableInstanceId' => $this->variableInstanceId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->variableInstanceId = $data['variableInstanceId'];
    }

    public function execute(CommandContext $commandContext, ...$args)
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
            if ($variable->getProcessDefinitionId() !== null) {
                $definition = $commandContext->getProcessEngineConfiguration()->getDeploymentCache()->findDeployedProcessDefinitionById(
                    $variable->getProcessDefinitionId()
                );
            }/*elseif ($variable.getCaseDefinitionId() !== null) {
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

    public function isRetryable(): bool
    {
        return false;
    }
}
