<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    PropertyChange,
    PropertyEntity,
    PropertyManager
};

class DeletePropertyCmd implements CommandInterface
{
    protected $name;

    /**
     * @param name
     */
    public function __construct(?string $name)
    {
        $this->name = $name;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkDeleteProperty");

        $propertyManager = $commandContext->getPropertyManager();

        $propertyEntity = $propertyManager->findPropertyById($this->name);

        if ($propertyEntity !== null) {
            $propertyManager->delete($propertyEntity);

            $commandContext->getOperationLogManager()->logPropertyOperation(
                UserOperationLogEntryInterface::OPERATION_TYPE_DELETE,
                [new PropertyChange("name", null, $this->name)]
            );
        }

        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
