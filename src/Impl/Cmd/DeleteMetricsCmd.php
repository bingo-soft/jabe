<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\PropertyChange;

class DeleteMetricsCmd implements CommandInterface, \Serializable
{
    protected $timestamp;
    protected $reporter;

    public function __construct(?string $timestamp, ?string $reporter)
    {
        $this->timestamp = $timestamp;
        $this->reporter = $reporter;
    }

    public function serialize()
    {
        return json_encode([
            'timestamp' => $this->timestamp,
            'reporter' => $this->reporter
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->timestamp = $json->timestamp;
        $this->reporter = $json->reporter;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkDeleteMetrics");

        $this->writeUserOperationLog($commandContext);

        if ($this->timestamp === null && $this->reporter === null) {
            $commandContext->getMeterLogManager()
            ->deleteAll();
        } else {
            $commandContext->getMeterLogManager()
            ->deleteByTimestampAndReporter($this->timestamp, $this->reporter);
        }
        return null;
    }

    public function writeUserOperationLog(CommandContext $commandContext)
    {
        $propertyChanges = [];
        if ($this->timestamp !== null) {
            $propertyChanges[] = new PropertyChange("timestamp", null, $this->timestamp);
        }
        if ($this->reporter !== null) {
            $propertyChanges[] = new PropertyChange("reporter", null, $this->reporter);
        }
        if (empty($propertyChanges)) {
            $propertyChanges[] = PropertyChange::emptyChange();
        }
        $commandContext->getOperationLogManager()->logMetricsOperation(
            UserOperationLogEntryInterface::OPERATION_TYPE_DELETE,
            $propertyChanges
        );
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
