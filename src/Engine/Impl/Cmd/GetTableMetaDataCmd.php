<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class GetTableMetaDataCmd implements CommandInterface, \Serializable
{
    protected $tableName;

    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
    }

    public function serialize()
    {
        return json_encode([
            'tableName' => $this->tableName
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->tableName = $json->tableName;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("tableName", "tableName", $this->tableName);

        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkReadTableMetaData");

        return $commandContext
            ->getTableDataManager()
            ->getTableMetaData($this->tableName);
    }
}
