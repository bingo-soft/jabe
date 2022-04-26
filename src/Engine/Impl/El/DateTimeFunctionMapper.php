<?php

namespace Jabe\Engine\Impl\El;

use Jabe\Engine\Impl\Util\El\FunctionMapper;
use Jabe\Engine\Impl\Util\ReflectUtil;

class DateTimeFunctionMapper extends FunctionMapper
{
    public static $DATE_TIME_FUNCTION_MAP = null;

    public function resolveFunction(string $prefix, string $localName): ?\ReflectionMethod
    {
        // Context functions are used un-prefixed
        $this->ensureContextFunctionMapInitialized();
        if (array_key_exists($localName, self::$DATE_TIME_FUNCTION_MAP)) {
            return self::$DATE_TIME_FUNCTION_MAP[$localName];
        }
        return null;
    }

    protected function ensureContextFunctionMapInitialized(): void
    {
        if (self::$DATE_TIME_FUNCTION_MAP == null) {
            self::$DATE_TIME_FUNCTION_MAP = [];
            $this->createMethodBindings();
        }
    }

    protected function createMethodBindings(): void
    {
        $mapperClass = get_class($this);
        self::$DATE_TIME_FUNCTION_MAP["now"] = ReflectUtil::getMethod($mapperClass, "now");
        self::$DATE_TIME_FUNCTION_MAP["dateTime"] = ReflectUtil::getMethod($mapperClass, "dateTime");
    }

    public static function now(): string
    {
        return time();
    }

    public static function dateTime(): \DateTime
    {
        return new \DateTime('NOW');
    }
}
