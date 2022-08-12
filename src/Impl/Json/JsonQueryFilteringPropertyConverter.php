<?php

namespace Jabe\Impl\Json;

use Jabe\Impl\{
    QueryEntityRelationCondition,
    QueryPropertyImpl
};
use Jabe\Impl\Util\JsonUtil;

class JsonQueryFilteringPropertyConverter extends JsonObjectConverter
{
    protected static $INSTANCE;// = new JsonQueryFilteringPropertyConverter();

    protected static $ARRAY_CONVERTER;// = new JsonArrayOfObjectsConverter<>(INSTANCE);

    public const BASE_PROPERTY = "baseField";
    public const COMPARISON_PROPERTY = "comparisonField";
    public const SCALAR_VALUE = "value";

    public static function instance(): JsonQueryFilteringPropertyConverter
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new JsonQueryFilteringPropertyConverter();
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

    public function toJsonObject(/*QueryEntityRelationCondition*/$filteringProperty, bool $isOrQueryActive = false): ?\stdClass
    {
        $jsonObject = JsonUtil::createObject();

        JsonUtil::addField($jsonObject, self::BASE_PROPERTY, $filteringProperty->getProperty()->getName());

        $comparisonProperty = $filteringProperty->getComparisonProperty();
        if ($comparisonProperty !== null) {
            JsonUtil::addField($jsonObject, self::COMPARISON_PROPERTY, $comparisonProperty->getName());
        }

        $scalarValue = $filteringProperty->getScalarValue();
        if ($scalarValue !== null) {
            JsonUtil::addFieldRawValue($jsonObject, self::SCALAR_VALUE, $scalarValue);
        }

        return $jsonObject;
    }

    public function toObject(\stdClass $jsonObject, bool $isOrQuery = false)
    {
        // this is limited in that it allows only String values;
        // that is sufficient for current use case with task filters
        // but could be extended by a data type in the future
        $scalarValue = null;
        if (property_exists($jsonObject, self::SCALAR_VALUE)) {
            $scalarValue = JsonUtil::getString($jsonObject, self::SCALAR_VALUE);
        }

        $baseProperty = null;
        if (property_exists($jsonObject, self::BASE_PROPERTY)) {
            $baseProperty = new QueryPropertyImpl(JsonUtil::getString($jsonObject, self::BASE_PROPERTY));
        }

        $comparisonProperty = null;
        if (property_exists($jsonObject, self::COMPARISON_PROPERTY)) {
            $comparisonProperty = new QueryPropertyImpl(JsonUtil::getString($jsonObject, self::COMPARISON_PROPERTY));
        }

        return new QueryEntityRelationCondition($baseProperty, $comparisonProperty, $scalarValue);
    }
}
