<?php

namespace BpmPlatform\Engine\Impl\Language;

abstract class TypeConverter
{
    /**
     * Default conversions.
     */
    public static $DEFAULT;

    private function __construct()
    {
    }

    public static function getDefault(): TypeConverter
    {
        if (self::$DEFAULT == null) {
            self::$DEFAULT = new TypeConverterImpl();
        }
        return self::$DEFAULT;
    }

    /**
     * Convert the given input value to the specified target type.
     * @param value input value
     * @param type target type
     * @return conversion result
     */
    abstract public function convert($value, string $type);
}
