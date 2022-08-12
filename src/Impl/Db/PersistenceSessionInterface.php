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

    public function selectList(string $statement, array $params = [], array $types = []);

    public function selectById(string $type, string $id);

    public function selectOne(string $statement, array $params = [], array $types = []);

    public function lock(string $statement, array $params = [], array $types = []): void;

    public function executeNonEmptyUpdateStmt(string $statement, array $params = [], array $types = []);

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
