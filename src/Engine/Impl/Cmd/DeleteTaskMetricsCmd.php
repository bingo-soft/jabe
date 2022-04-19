<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Entity\PropertyChange;

class DeleteTaskMetricsCmd implements CommandInterface, \Serializable
{
    protected $timestamp;

    public function __construct(?string $timestamp)
    {
        $this->timestamp = $timestamp;
    }

    public function serialize()
    {
        return json_encode([
            'timestamp' => $this->timestamp
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->timestamp = $json->timestamp;
    }

    public function execute(CommandContext $commandContext)
    {
        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkDeleteTaskMetrics");

        $this->writeUserOperationLog($commandContext);
        $commandContext->getMeterLogManager()->deleteTaskMetricsByTimestamp($this->timestamp);
        return null;
    }

    protected function writeUserOperationLog(CommandContext $commandContext): void
    {
        $propertyChanges = [];
        if ($this->timestamp != null) {
            $propertyChanges[] = new PropertyChange("timestamp", null, $this->timestamp);
        }
        if (empty($propertyChanges)) {
            $propertyChanges[] = PropertyChange::emptyChange();
        }
        $commandContext->getOperationLogManager()->logTaskMetricsOperation(
            UserOperationLogEntryInterface::OPERATION_TYPE_DELETE,
            $propertyChanges
        );
    }
}
