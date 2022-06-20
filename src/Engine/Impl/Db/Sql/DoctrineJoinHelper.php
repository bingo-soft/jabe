<?php

namespace Jabe\Engine\Impl\Db\Sql;

use Jabe\Engine\Impl\{
    ProcessEngineLogger,
    QueryOrderingProperty
};
use Jabe\Engine\Impl\Db\EnginePersistenceLogger;

class DoctrineJoinHelper
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;
    public const DEFAULT_ORDER = "RES.ID_ asc";
    public static $mappings;// = new HashMap<String, MyBatisTableMapping>();

    private static function init(): void
    {
        if (empty(self::$mappings)) {
            self::$mappings = [
                QueryOrderingProperty::RELATION_VARIABLE => new VariableTableMapping(),
                QueryOrderingProperty::RELATION_PROCESS_DEFINITION => new ProcessDefinitionTableMapping(),
                //QueryOrderingProperty::RELATION_CASE_DEFINITION => new CaseDefinitionTableMapping(),
                QueryOrderingProperty::RELATION_DEPLOYMENT => new DeploymentTableMapping()
            ];
        }
    }

    public static function tableAlias(?string $relation, int $index): string
    {
        self::init();
        if ($relation == null) {
            return "RES";
        } else {
            $mapping = self::getTableMapping($relation);
            if ($mapping->isOneToOneRelation()) {
                return $mapping->getTableAlias();
            } else {
                return $mapping->getTableAlias() . $index;
            }
        }
    }

    public static function tableMapping(string $relation): string
    {
        self::init();
        $mapping = self::getTableMapping($relation);

        return $mapping->getTableName();
    }

    public static function orderBySelection(QueryOrderingProperty $orderingProperty, int $index): string
    {
        self::init();
        $queryProperty = $orderingProperty->getQueryProperty();

        $sb = "";

        if ($queryProperty->getFunction() != null) {
            $sb .= $queryProperty->getFunction();
            $sb .= "(";
        }

        $sb .= self::tableAlias($orderingProperty->getRelation(), $index);
        $sb .= ".";
        $sb .= $queryProperty->getName();

        if ($queryProperty->getFunction() != null) {
            $sb .= ")";
        }

        return $sb;
    }

    public static function orderBy(QueryOrderingProperty $orderingProperty, int $index): string
    {
        self::init();
        $queryProperty = $orderingProperty->getQueryProperty();

        $sb = "";

        $sb .= self::tableAlias($orderingProperty->getRelation(), $index);
        if ($orderingProperty->isContainedProperty()) {
            $sb .= ".";
        } else {
            $sb .= "_";
        }
        $sb .= $queryProperty->getName();

        $sb .= " ";

        $sb .= $orderingProperty->getDirection()->getName();

        return $sb;
    }

    protected static function getTableMapping(string $relation): DoctrineTableMappingInterface
    {
        self::init();
        if (array_key_exists($relation, self::$mappings)) {
            return self::$mappings[$relation];
        } else {
            //throw LOG.missingRelationMappingException(relation);
            throw new \Exception("missingRelationMappingException");
        }
    }
}
