<?php

namespace Jabe\Engine\Impl\Util;

use Jabe\Engine\Impl\Json\JsonObjectConverter;

class JsonUtil
{
    public static function addFieldRawValue(\stdClass $jsonObject, string $memberName, $rawValue = null): void
    {
        if ($rawValue !== null) {
            $jsonObject->{$memberName} = json_decode($rawValue);
        }
    }

    public static function addNullField(\stdClass $jsonObject, string $name): void
    {
        $jsonObject->{$name} = null;
    }

    public static function addField(\stdClass $jsonObject, string $name, $converterOrValue = null, $value = null): void
    {
        if ($converterOrValue !== null && $converterOrlist instanceof JsonObjectConverter) {
            $jsonObject->{$name} = $converterOrValue->toJsonObject($value);
        } elseif ($converterOrValue !== null) {
            $jsonObject->{$name} = $converterOrValue;
        }
    }

    public static function addListField(\stdClass $jsonObject, string $name, $converterOrlist, $list = null): void
    {
        if ($converterOrlist !== null && is_array($converterOrlist)) {
            $jsonObject->{$name} = $converterOrlist;
        } elseif ($converterOrlist !== null && $converterOrlist instanceof JsonObjectConverter && $list !== null) {
            $arrayNode = [];
            foreach ($list as $item) {
                if ($item !== null) {
                    $jsonElement = $converterOrlist->toJsonObject($item);
                    $arrayNode[] = $jsonElement;
                }
            }
            $jsonObject->{$name} = $arrayNode;
        }
    }

    public static function addArrayField(\stdClass $jsonObject, string $name, array $array = null): void
    {
        if ($array !== null) {
            self::addListField($jsonObject, $name, $array);
        }
    }

    public static function addDateField(\stdClass $jsonObject, string $name, $date = null): void
    {
        if ($date !== null) {
            if (is_string($date)) {
                $jsonObject->{$name} = $date;
            } elseif ($date instanceof \DateTime) {
                $jsonObject->{$name} = $date->format('c');
            }
        }
    }

    public static function addElement(array &$jsonObject, JsonObjectConverter $converter, $value = null): void
    {
        if ($value !== null) {
            $jsonElement = $converter->toJsonObject($value);
            if ($jsonElement !== null) {
                $jsonObject[] = $jsonElement;
            }
        }
    }

    public static function asString(?array $properties = null): string
    {
        if (!empty($properties)) {
            return json_encode($properties);
        }
        return "";
    }

    public static function asPhpObject(\stdClass $jsonObject = null, JsonObjectConverter $converter = null)
    {
        if ($jsonObject !== null && $converter !== null) {
            return $converter->toObject($jsonObject);
        } else {
            return null;
        }
    }

    public static function asStringList($jsonObject = []): array
    {
        if (empty($jsonObject)) {
            return [];
        }

        $list = [];
        foreach ($jsonObject as $entry) {
            $stringValue = null;
            try {
                $stringValue = json_encode($entry);
            } catch (\Exception $e) {
                //LOG.logJsonException(e);
            }

            if ($stringValue !== null) {
                $list[] = $stringValue;
            }
        }

        return $list;
    }

    public static function asList($jsonArray = [], JsonObjectConverter $converter = null, $listSupplier = null)
    {
        if (empty($jsonArray) || $converter === null) {
            return [];
        }

        $list = null;
        if (is_string($listSupplier) && class_exists($listSupplier)) {
            $list = new $listSupplier();
        } else {
            $list = [];
        }

        foreach ($jsonArray as $element) {
            $jsonObject = null;
            try {
                $jsonObject = (object) $element;
            } catch (\Exception $e) {
                //LOG.logJsonException(e);
            }

            if ($jsonObject !== null) {
                $rawObject = $converter->toObject($jsonObject);
                if ($rawObject !== null) {
                    if (is_array($list)) {
                        $list[] = $rawObject;
                    } elseif ($list !== null && method_exists($list, 'add')) {
                        $list->add($rawObject);
                    }
                }
            }
        }

        return $list;
    }

    public static function getArray($json = null, string $memberName = null): array
    {
        if ($json !== null && $json instanceof \stdClass && $memberName !== null && property_exists($json, $memberName)) {
            return self::getArray($json->{$memberName});
        } elseif ($json !== null && is_array($json)) {
            return $json;
        } else {
            return self::createArray();
        }
    }

    public static function getObject(\stdClass $json, ?string $memberName = null): \stdClass
    {
        if ($json !== null && $memberName === null) {
            return $json;
        }
        if ($json !== null && $memberName !== null && property_exists($json, $memberName)) {
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

    public static function getBoolean(\stdClass $json = null, string $memberName = null): bool
    {
        if ($json !== null && $memberName !== null && property_exists($json, $memberName)) {
            try {
                return boolval(json_decode($json->{$memberName}));
            } catch (\Exception $e) {
                //LOG.logJsonException(e);
                return false;
            }
        } else {
            return false;
        }
    }

    public static function getString($json = null, string $memberName = null, string $defaultString = null): string
    {
        if (is_object($json)) {
            if ($json !== null && $memberName !== null && property_exists($json, $memberName)) {
                return self::getString($json->{$memberName});
            } else {
                return $defaultString;
            }
        } else {
            try {
                return json_encode($json);
            } catch (\Exception $e) {
                return "";
            }
        }
    }

    public static function getInt(\stdClass $json = null, string $memberName = null): int
    {
        if ($json !== null && $memberName !== null && property_exists($json, $memberName)) {
            try {
                return intval($json->{$memberName});
            } catch (\Exception $e) {
                //LOG.logJsonException(e);
                return 0;
            }
        } else {
            return 0;
        }
    }

    public static function isNull(\stdClass $jsonObject = null, string $memberName = null): bool
    {
        if ($jsonObject !== null && $memberName !== null && property_exists($jsonObject, $memberName)) {
            return $jsonObject->{$memberName} === null;
        } else {
            return false;
        }
    }

    public static function getLong(\stdClass $json = null, string $memberName = null): int
    {
        return self::getInt($json, $memberName);
    }
}
