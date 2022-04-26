<?php

namespace Jabe\Engine\Impl\Util;

class JsonUtil
{
    public static function asString(?array $properties = null): string
    {
        if (!empty($properties)) {
            return json_encode($properties);
        }
        return "";
    }

    public static function getObject(\stdClass $json, ?string $memberName = null): \stdClass
    {
        if ($json != null && $memberName == null) {
            return $json;
        }
        if ($json != null && $memberName != null && property_exists($json, $memberName)) {
            return $json->{$memberName};
        } else {
            return self::createObject();
        }
    }

    public static function createObject(): \stdClass
    {
        return new \stdClass();
    }

    public static function createArray(): array
    {
        return [];
    }
}
