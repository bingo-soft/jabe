<?php

namespace Tests\Bpmn\Engine\Language;

class SimpleClass
{
    public $prop = true;

    public static function sin($value): float
    {
        return sin($value);
    }

    public static function cos($value): float
    {
        return cos($value);
    }

    public static function inArray($needle, array $haystack): bool
    {
        return in_array($needle, $haystack);
    }

    public function foo(): int
    {
        return 1;
    }

    public function bar(): int
    {
        return 2;
    }
}
