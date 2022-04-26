<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\BadUserRequestException;
use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\{
    ExecutionEntity,
    PropertyChange
};
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Runtime\IncidentInterface;

class CreateIncidentCmd implements CommandInterface
{
    protected $incidentType;
    protected $executionId;
    protected $configuration;
    protected $message;

    public function __construct(string $incidentType, string $executionId, string $configuration, string $message)
    {
        $this->incidentType = $incidentType;
        $this->executionId = $executionId;
        $this->configuration = $configuration;
        $this->message = $message;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("Execution id cannot be null", "executionId", $this->executionId);
        EnsureUtil::ensureNotNull(BadUserRequestException::class, "incidentType", $this->incidentType);

        $execution = $commandContext->getExecutionManager()->findExecutionById($this->executionId);
        EnsureUtil::ensureNotNull(
            "Cannot find an execution with executionId '" . $this->executionId . "'",
            "execution",
            $execution
        );
        EnsureUtil::ensureNotNull(
            "Execution must be related to an activity",
            "activity",
            $execution->getActivity()
        );

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkUpdateProcessInstance($execution);
        }

        $propertyChanges = [];
        $propertyChanges[] = new PropertyChange("incidentType", null, $this->incidentType);
        $propertyChanges[] = new PropertyChange("configuration", null, $this->configuration);

        $commandContext->getOperationLogManager()->logProcessInstanceOperation(
            UserOperationLogEntryInterface::OPERATION_TYPE_CREATE_INCIDENT,
            $execution->getProcessInstanceId(),
            $execution->getProcessDefinitionId(),
            null,
            $propertyChanges
        );

        return $execution->createIncident($this->incidentType, $this->configuration, $this->message);
    }
}
