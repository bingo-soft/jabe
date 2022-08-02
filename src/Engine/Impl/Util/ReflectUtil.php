<?php

namespace Jabe\Engine\Impl\Util;

use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Engine\Impl\Context\Context;

class ReflectUtil
{
    //private static final EngineUtilLogger LOG = ProcessEngineLogger.UTIL_LOGGER;

    private const CHAR_ENCODINGS = [
        "ä" => "%C3%A4",
        "ö" => "%C3%B6",
        "ü" => "%C3%BC",
        "Ä" => "%C3%84",
        "Ö" => "%C3%96",
        "Ü" => "%C3%9C"
    ];

    public static function getResourceAsStream(string $name)
    {
        $resourceStream = null;
        if (file_exists($name)) {
            $resourceStream = fopen($name, 'r+');
        }
        return $resourceStream;
    }

    public static function getResource(string $name): ?string
    {
        return file_exists($name) ? $name : null;
    }

    public static function getResourceUrlAsString(string $name): string
    {
        $url = self::getResource($name);
        foreach (self::CHAR_ENCODINGS as $key => $value) {
            $url = str_replace($key, $value, $url);
        }
        return $url;
    }

    public static function instantiate(string $className)
    {
        try {
            return new $className();
        } catch (\Exception $e) {
            //throw LOG.exceptionWhileInstantiatingClass(className, e);
            throw new \Exception(sprintf("exceptionWhileInstantiatingClass %s", $className));
        }
    }

    /**
     * Returns the field of the given object or null if it doesnt exist.
     */
    public static function getField(string $fieldName, $object): ?\ReflectionProperty
    {
        try {
            $ref = new \ReflectionClass($object);
            return $ref->getProperty($fieldName);
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function setField(\ReflectionProperty $field, $object, $value): void
    {
        try {
            $field->setValue($value);
        } catch (\Exception $e) {
            //throw LOG.exceptionWhileSettingField(field, object, value, e);
            throw $e;
        }
    }

    /**
     * Returns the setter-method for the given field name or null if no setter exists.
     */
    public static function getSetter(string $fieldName, string $clazz): ?\ReflectionMethod
    {
        $setterName = self::buildSetterName($fieldName);
        try {
            // Using getMathods(), getMathod(...) expects exact parameter type
            // matching and ignores inheritance-tree.
            $ref = new \ReflectionClass($clazz);
            $methods = $ref->getMethods();
            foreach ($methods as $method) {
                if ($method->name == $setterName) {
                    return $method;
                }
            }
            return null;
        } catch (\Exception $e) {
            //throw LOG.unableToAccessMethod(setterName, clazz.getName());
            throw new \Exception("unableToAccessMethod");
        }
    }

    private static function buildSetterName(string $fieldName): string
    {
        return "set" . strtoupper($fieldName[0]) . substr($fieldName, 1);
    }

    /**
     * Finds a method by name
     *
     * @param declaringType the name of the class
     * @param methodName the name of the method to look for
     */
    public static function getMethod(string $declaringType, string $methodName): ?\ReflectionMethod
    {
        try {
            $ref = new \ReflectionClass($declaringType);
            $methods = $ref->getMethods();
            foreach ($methods as $method) {
                if ($method->name == $methodName) {
                    return $method;
                }
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
