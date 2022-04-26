<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\BadUserRequestException;
use Jabe\Engine\Exception\NotFoundException;
use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\PropertyChange;
use Jabe\Engine\Impl\Util\EnsureUtil;

class ResolveIncidentCmd implements CommandInterface
{
    protected $incidentId;

    public function __construct(?string $incidentId)
    {
        EnsureUtil::ensureNotNull(BadUserRequestException::class, "incidentId", $incidentId);
        $this->incidentId = $incidentId;
    }

    public function execute(CommandContext $commandContext)
    {
        $incident = $commandContext->getIncidentManager()->findIncidentById($this->incidentId);

        EnsureUtil::ensureNotNull(
            "Cannot find an incident with id '" . $this->incidentId . "'",
            "incident",
            $incident
        );

        if ($incident->getIncidentType() == "failedJob" || $incident->getIncidentType() == "failedExternalTask") {
            throw new BadUserRequestException("Cannot resolve an incident of type " . $incident->getIncidentType());
        }

        EnsureUtil::ensureNotNull(BadUserRequestException::class, "executionId", $incident->getExecutionId());
        $execution = $commandContext->getExecutionManager()->findExecutionById($incident->getExecutionId());

        EnsureUtil::ensureNotNull("Cannot find an execution for an incident with id '" . $this->incidentId . "'", "execution", $execution);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkUpdateProcessInstance($execution);
        }

        $commandContext->getOperationLogManager()->logProcessInstanceOperation(
            UserOperationLogEntryInterface::OPERATION_TYPE_RESOLVE,
            $execution->getProcessInstanceId(),
            $execution->getProcessDefinitionId(),
            null,
            [new PropertyChange("incidentId", null, $this->incidentId)]
        );

        $execution->resolveIncident($this->incidentId);
        return null;
    }
}
