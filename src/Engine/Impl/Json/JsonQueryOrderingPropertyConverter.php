<?php

namespace Jabe\Engine\Impl\Json;

use Jabe\Engine\Impl\{
    Direction,
    QueryEntityRelationCondition,
    QueryOrderingProperty,
    VariableOrderProperty
};
use Jabe\Engine\Impl\Util\JsonUtil;

class JsonQueryOrderingPropertyConverter extends JsonObjectConverter
{

    protected static $INSTANCE;

    protected static $ARRAY_CONVERTER;

    public const RELATION = "relation";
    public const QUERY_PROPERTY = "queryProperty";
    public const QUERY_PROPERTY_FUNCTION = "queryPropertyFunction";
    public const DIRECTION = "direction";
    public const RELATION_CONDITIONS = "relationProperties";

    public static function instance(): JsonQueryOrderingPropertyConverter
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new JsonQueryOrderingPropertyConverter();
        }
        return self::$INSTANCE;
    }

    public static function arrayConverter(): JsonArrayOfObjectsConverter
    {
        if (self::$ARRAY_CONVERTER === null) {
            self::$ARRAY_CONVERTER = new JsonArrayOfObjectsConverter(self::instance());
        }
        return self::$ARRAY_CONVERTER;
    }

    public function toJsonObject(/*QueryOrderingProperty*/$property, bool $isOrQueryActive = false): ?\stdClass
    {
        $jsonObject = JsonUtil::createObject();

        JsonUtil::addField($jsonObject, self::RELATION, $property->getRelation());

        $queryProperty = $property->getQueryProperty();
        if ($queryProperty !== null) {
            JsonUtil::addField($jsonObject, self::QUERY_PROPERTY, $queryProperty->getName());
            JsonUtil::addField($jsonObject, self::QUERY_PROPERTY_FUNCTION, $queryProperty->getFunction());
        }

        $direction = $property->getDirection();
        if ($direction !== null) {
            JsonUtil::addField($jsonObject, self::DIRECTION, $direction->getName());
        }

        if ($property->hasRelationConditions()) {
            $relationConditionsJson = JsonQueryFilteringPropertyConverter::arrayConverter()
                                      ->toJsonArray($property->getRelationConditions());
            JsonUtil::addField($jsonObject, self::RELATION_CONDITIONS, $relationConditionsJson);
        }

        return $jsonObject;
    }

    public function toObject(\stdClass $jsonObject, bool $isOrQuery = false)
    {
        $relation = null;
        if (property_exists($jsonObject, self::RELATION)) {
            $relation = JsonUtil::getString($jsonObject, self::RELATION);
        }

        $property = null;
        if (QueryOrderingProperty::RELATION_VARIABLE == $relation) {
            $property = new VariableOrderProperty();
        } else {
            $property = new QueryOrderingProperty();
        }

        $property->setRelation($relation);

        if (property_exists($jsonObject, self::QUERY_PROPERTY)) {
            $propertyName = JsonUtil::getString($jsonObject, self::QUERY_PROPERTY);
            $propertyFunction = null;
            if (property_exists($jsonObject, self::QUERY_PROPERTY_FUNCTION)) {
                $propertyFunction = JsonUtil::getString($jsonObject, self::QUERY_PROPERTY_FUNCTION);
            }

            $queryProperty = new QueryPropertyImpl($propertyName, $propertyFunction);
            $property->setQueryProperty($queryProperty);
        }

        if (property_exists($jsonObject, self::DIRECTION)) {
            $direction = JsonUtil::getString($jsonObject, self::DIRECTION);
            $property->setDirection(Direction::findByName($direction));
        }

        if (property_exists($jsonObject, self::RELATION_CONDITIONS)) {
            $relationConditions =
                JsonQueryFilteringPropertyConverter::arrayConverter()->toObject(JsonUtil::getArray($jsonObject, self::RELATION_CONDITIONS));
            $property->setRelationConditions($relationConditions);
        }

        return $property;
    }
}
