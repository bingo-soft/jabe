<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class GetTableMetaDataCmd implements CommandInterface
{
    protected $tableName;

    public function __construct(?string $tableName)
    {
        $this->tableName = $tableName;
    }

    public function __serialize(): array
    {
        return [
            'tableName' => $this->tableName
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->tableName = $data['tableName'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureNotNull("tableName", "tableName", $this->tableName);

        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkReadTableMetaData");

        return $commandContext
            ->getTableDataManager()
            ->getTableMetaData($this->tableName);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
