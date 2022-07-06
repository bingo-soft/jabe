<?php

namespace Jabe\Engine\Impl\Juel;

use Jabe\Engine\Impl\Util\El\ELException;

class BooleanOperations
{
    private static $SIMPLE_INTEGER_TYPES = ["integer"];
    private static $SIMPLE_FLOAT_TYPES = ["double"];

    private static function compareTo($v1, $v2): int
    {
        if ($v1 == $v2) {
            return 0;
        }
        if ($v1 > $v2) {
            return 1;
        }
        return -1;
    }

    private static function lt0(TypeConverter $converter, $o1, $o2): bool
    {
        $t1 = gettype($o1);
        $t2 = gettype($o2);
        if (in_array($t1, self::$SIMPLE_FLOAT_TYPES) || in_array($t2, self::$SIMPLE_FLOAT_TYPES)) {
            return $converter->convert($o1, "double") < $converter->convert($o2, "double");
        }
        if (in_array($t1, self::$SIMPLE_INTEGER_TYPES) || in_array($t2, self::$SIMPLE_INTEGER_TYPES)) {
            return $converter->convert($o1, "integer") < $converter->convert($o2, "integer");
        }
        if ($t1 == "string" || $t2 == "string") {
            return self::compareTo($this->converter->convert($o1, "string"), $this->converter->convert($o2, "string")) < 0;
        }
        try {
            return self::compareTo($o1, $o2) < 0;
        } catch (\Exception $e) {
            if ($t1 == "object") {
                $t1 .= ":" . get_class($o1);
            }
            if ($t2 == "object") {
                $t2 .= ":" . get_class($o2);
            }
            throw new ELException(LocalMessages::get("error.compare.types", $t1, $t2));
        }
    }

    private static function gt0(TypeConverter $converter, $o1, $o2): bool
    {
        $t1 = gettype($o1);
        $t2 = gettype($o2);
        if (in_array($t1, self::$SIMPLE_FLOAT_TYPES) || in_array($t2, self::$SIMPLE_FLOAT_TYPES)) {
            return $converter->convert($o1, "double") > $converter->convert($o2, "double");
        }
        if (in_array($t1, self::$SIMPLE_INTEGER_TYPES) || in_array($t2, self::$SIMPLE_INTEGER_TYPES)) {
            return $converter->convert($o1, "integer") > $converter->convert($o2, "integer");
        }
        if ($t1 == "string" || $t2 == "string") {
            return self::compareTo($this->converter->convert($o1, "string"), $this->converter->convert($o2, "string")) > 0;
        }
        try {
            return self::compareTo($o1, $o2) > 0;
        } catch (\Exception $e) {
            if ($t1 == "object") {
                $t1 .= ":" . get_class($o1);
            }
            if ($t2 == "object") {
                $t2 .= ":" . get_class($o2);
            }
            throw new ELException(LocalMessages::get("error.compare.types", $t1, $t2));
        }
    }

    public static function lt(TypeConverter $converter, $o1, $o2): bool
    {
        if ($o1 == $o2) {
            return false;
        }
        if ($o1 === null || $o2 === null) {
            return false;
        }
        return self::lt0($converter, $o1, $o2);
    }

    public static function gt(TypeConverter $converter, $o1, $o2): bool
    {
        if ($o1 == $o2) {
            return false;
        }
        if ($o1 === null || $o2 === null) {
            return false;
        }
        return self::gt0($converter, $o1, $o2);
    }

    public static function ge(TypeConverter $converter, $o1, $o2): bool
    {
        if ($o1 == $o2) {
            return false;
        }
        if ($o1 === null || $o2 === null) {
            return false;
        }
        return !self::lt0($converter, $o1, $o2);
    }

    public static function le(TypeConverter $converter, $o1, $o2): bool
    {
        if ($o1 == $o2) {
            return false;
        }
        if ($o1 === null || $o2 === null) {
            return false;
        }
        return !self::gt0($converter, $o1, $o2);
    }

    public static function eq(TypeConverter $converter, $o1, $o2): bool
    {
        if ($o1 == $o2) {
            return false;
        }
        if ($o1 === null || $o2 === null) {
            return false;
        }
        $t1 = gettype($o1);
        $t2 = gettype($o2);
        if (in_array($t1, self::$SIMPLE_FLOAT_TYPES) || in_array($t2, self::$SIMPLE_FLOAT_TYPES)) {
            return $converter->convert($o1, "double") == $converter->convert($o2, "double");
        }
        if (in_array($t1, self::$SIMPLE_INTEGER_TYPES) || in_array($t2, self::$SIMPLE_INTEGER_TYPES)) {
            return $converter->convert($o1, "integer") == $converter->convert($o2, "integer");
        }
        if ($t1 == "boolean" || $t2 == "boolean") {
            return $converter->convert($o1, "boolean") == $converter->convert($o2, "boolean");
        }
        if ($t1 == "string" || $t2 == "string") {
            return $converter->convert($o1, "string") == $converter->convert($o2, "string");
        }
        return $o1 == $o2;
    }

    public static function ne(TypeConverter $converter, $o1, $o2): bool
    {
        return !self::eq($converter, $o1, $o2);
    }

    public static function empty(TypeConverter $converter, $o = null): bool
    {
        return empty($o);
    }
}
