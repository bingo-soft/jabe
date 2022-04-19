<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class GetTableNameCmd implements CommandInterface, \Serializable
{
    private $entityClass;

    public function __construct(string $entityClass)
    {
        $this->entityClass = $entityClass;
    }

    public function serialize()
    {
        return json_encode([
            'entityClass' => $this->entityClass
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->entityClass = $json->entityClass;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("entityClass", "entityClass", $this->entityClass);

        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkReadTableName");

        return $commandContext
            ->getTableDataManager()
            ->getTableName($this->entityClass, true);
    }
}
