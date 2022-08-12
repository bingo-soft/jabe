<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetPropertiesCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext)
    {
        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkReadProperties");

        $propertyEntities = $commandContext
            ->getDbEntityManager()
            ->selectList("selectProperties");

        $properties = [];
        foreach ($propertyEntities as $propertyEntity) {
            $properties[$propertyEntity->getName()] = $propertyEntity->getValue();
        }
        return $properties;
    }
}
