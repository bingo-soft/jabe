<?php

namespace BpmPlatform\Engine\Impl\Juel;

use BpmPlatform\Engine\Impl\Util\El\ELException;

class NumberOperations
{
    private static $LONG_ZERO = 0;

    private static function isDotEe(string $value): bool
    {
        $length = strlen($value);
        for ($i = 0; $i < $length; $i++) {
            switch ($value[$i]) {
                case '.':
                case 'E':
                case 'e':
                    return true;
            }
        }
        return false;
    }

    private static function isFloatOrDouble($value): bool
    {
        return gettype($value) == "double";
    }

    private static function isFloatOrDoubleOrDotEe($value): bool
    {
        return self::isFloatOrDouble($value) || self::isDotEe(strval($value));
    }

    private static function isBigDecimalOrBigInteger($value): bool
    {
        return gettype($value) == "integer";
    }

    private static function isBigDecimalOrFloatOrDoubleOrDotEe($value): bool
    {
        return self::isFloatOrDoubleOrDotEe($value);
    }

    public static function add(TypeConverter $converter, $o1 = null, $o2 = null)
    {
        if ($o1 == null && $o2 == null) {
            return self::$LONG_ZERO;
        }
        if (gettype($o1) == "double" || gettype($o2) == "double") {
            return $converter->convert($o1, "double") + $converter->convert($o2, "double");
        }
        if (self::isFloatOrDoubleOrDotEe($o1) || self::isFloatOrDoubleOrDotEe($o2)) {
            return $converter->convert($o1, "double") + $converter->convert($o2, "double");
        }
        return $converter->convert($o1, "integer") + $converter->convert($o2, "integer");
    }

    public static function sub(TypeConverter $converter, $o1 = null, $o2 = null)
    {
        if ($o1 == null && $o2 == null) {
            return self::$LONG_ZERO;
        }
        if (gettype($o1) == "double" || gettype($o2) == "double") {
            return $converter->convert($o1, "double") - $converter->convert($o2, "double");
        }
        if (self::isFloatOrDoubleOrDotEe($o1) || self::isFloatOrDoubleOrDotEe($o2)) {
            return $converter->convert($o1, "double") - $converter->convert($o2, "double");
        }
        return $converter->convert($o1, "integer") - $converter->convert($o2, "integer");
    }

    public static function mul(TypeConverter $converter, $o1 = null, $o2 = null)
    {
        if ($o1 == null && $o2 == null) {
            return self::$LONG_ZERO;
        }
        if (gettype($o1) == "double" || gettype($o2) == "double") {
            return $converter->convert($o1, "double") * $converter->convert($o2, "double");
        }
        if (self::isFloatOrDoubleOrDotEe($o1) || self::isFloatOrDoubleOrDotEe($o2)) {
            return $converter->convert($o1, "double") * $converter->convert($o2, "double");
        }
        return $converter->convert($o1, "integer") * $converter->convert($o2, "integer");
    }

    public static function div(TypeConverter $converter, $o1 = null, $o2 = null)
    {
        if ($o1 == null && $o2 == null) {
            return self::$LONG_ZERO;
        }
        return $converter->convert($o1, "double") / $converter->convert($o2, "double");
    }

    public static function mod(TypeConverter $converter, $o1 = null, $o2 = null)
    {
        if ($o1 == null && $o2 == null) {
            return self::$LONG_ZERO;
        }
        if (self::isBigDecimalOrFloatOrDoubleOrDotEe($o1) || self::isBigDecimalOrFloatOrDoubleOrDotEe(o2)) {
            return $converter->convert($o1, "double") % $converter->convert($o2, "double");
        }
        return $converter->convert($o1, "integer") % $converter->convert($o2, "integer");
    }

    public static function neg(TypeConverter $converter, $value)
    {
        if ($value == null) {
            return self::$LONG_ZERO;
        }
        if (gettype($value) == "double" || gettype($value) == "integer") {
            return -$value;
        }
        if (gettype($value) == "string") {
            if (self::isDotEe($value)) {
                return -$converter->convert($value, "double");
            }
            return -$converter->convert($value, "integer");
        }
        $type = gettype($value);
        if ($type == "object") {
            $type .= ":" . get_class($value);
        }
        throw new ELException(LocalMessages::get("error.negate", $type));
    }
}
