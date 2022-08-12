<?php

namespace Jabe\Impl\Util;

abstract class ClassNameUtil
{
    public static function getClassNameWithoutPackage(string $clazz): string
    {
        $path = explode('\\', $clazz);
        return array_pop($path);
    }
}
