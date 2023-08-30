<?php

namespace Jabe\Impl\Db;

use Jabe\Impl\Db\EntityManager\Operation\{
    DbOperation,
    DbOperationState
};
use Jabe\Impl\Interceptor\SessionInterface;

interface PersistenceSessionInterface extends SessionInterface
{
    public function executeDbOperations(array $operations): FlushResult;

    public function flushOperations(): void;

    public function selectList(?string $statement, $params = null);

    public function selectById(?string $type, ?string $id);

    public function selectOne(?string $statement, $params = null);

    public function lock(?string $statement, $params): void;

    public function executeNonEmptyUpdateStmt(?string $statement, $params = null): int;

    public function commit();

    public function rollback();

    // Schema Operations /////////////////////////////////

    public function dbSchemaCheckVersion(): void;

    public function dbSchemaCreate(): void;

    public function dbSchemaDrop(): void;

    public function dbSchemaPrune(): void;

    public function dbSchemaUpdate(): void;

    public function getTableNamesPresent(): array;

    // listeners //////////////////////////////////////////

    public function addEntityLoadListener(EntityLoadListenerInterface $listener): void;
}
