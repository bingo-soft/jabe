<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Management\{
    SchemaLogEntryInterface,
    SchemaLogQueryInterface
};
use Jabe\Engine\Query\QueryPropertyInterface;

class SchemaLogQueryImpl extends AbstractQuery implements SchemaLogQueryInterface
{
    private static $TIMESTAMP_PROPERTY;

    protected $version;

    public function __construct(CommandExecutorInterface $commandExecutor)
    {
        parent::__construct($commandExecutor);
    }

    public static function timestamp(): QueryPropertyImpl
    {
        if (self::$TIMESTAMP_PROPERTY == null) {
            self::$TIMESTAMP_PROPERTY = new QueryPropertyImpl("TIMESTAMP_");
        }
        return self::$TIMESTAMP_PROPERTY;
    }

    public function version(string $version): SchemaLogQueryInterface
    {
        EnsureUtil::ensureNotNull("version", "version", $version);
        $this->version = $version;
        return $this;
    }

    public function orderByTimestamp(): SchemaLogQueryInterface
    {
        $this->orderBy(self::timestamp());
        return $this;
    }

    public function executeCount(CommandContext $commandContext): int
    {
        $this->checkQueryOk();
        return $commandContext->getSchemaLogManager()->findSchemaLogEntryCountByQueryCriteria($this);
    }

    public function executeList(CommandContext $commandContext, Page $page): array
    {
        $this->checkQueryOk();
        return $commandContext->getSchemaLogManager()->findSchemaLogEntriesByQueryCriteria($this, $page);
    }
}
