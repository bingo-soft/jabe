<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\History\UserOperationLogEntry;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\{
    PropertyChange,
    PropertyEntity
};

class SetPropertyCmd implements CommandInterface
{
    protected $name;
    protected $value;

    public function __construct(string $name, string $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function execute(CommandContext $commandContext)
    {
        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkSetProperty");

        $propertyManager = $commandContext->getPropertyManager();

        $property = $propertyManager->findPropertyById($this->name);
        $operation = null;
        if ($property != null) {
            // update
            $property->setValue($this->value);
            $operation = UserOperationLogEntryInterface::OPERATION_TYPE_UPDATE;
        } else {
            // create
            $property = new PropertyEntity($this->name, $this->value);
            $propertyManager->insert($property);
            $operation = UserOperationLogEntryInterface::OPERATION_TYPE_CREATE;
        }

        $commandContext->getOperationLogManager()->logPropertyOperation(
            $operation,
            [new PropertyChange("name", null, $this->name)]
        );

        return null;
    }
}
