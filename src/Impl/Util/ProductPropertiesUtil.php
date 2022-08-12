<?php

namespace Jabe\Impl\Util;

class ProductPropertiesUtil
{
    public const PROPERTIES_FILE_PATH = "src/Engine/Resources/product-info.properties";
    public const VERSION_PROPERTY = "jabe.version";
    private static $INSTANCE;

    public static function instance(): array
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = PropertiesUtil::getProperties(self::PROPERTIES_FILE_PATH);
        }
        return self::$INSTANCE;
    }

    /**
     * @return the current version of the product
     */
    public static function getProductVersion(): string
    {
        return self::instance()[self::VERSION_PROPERTY];
    }
}
