<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class GetTableNameCmd implements CommandInterface
{
    private $entityClass;

    public function __construct(?string $entityClass)
    {
        $this->entityClass = $entityClass;
    }

    public function __serialize(): array
    {
        return [
            'entityClass' => $this->entityClass
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->entityClass = $data['entityClass'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureNotNull("entityClass", "entityClass", $this->entityClass);

        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkReadTableName");

        return $commandContext
            ->getTableDataManager()
            ->getTableName($this->entityClass, true);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
