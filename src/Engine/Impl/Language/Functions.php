<?php

namespace BpmPlatform\Engine\Impl\Language;

use BpmPlatform\Engine\Impl\Expression\FunctionMapper;

class Functions extends FunctionMapper
{
    private $map = [];

    public function resolveFunction(string $prefix, string $localName): ?\ReflectionMethod
    {
        $key = $prefix . ":" . $localName;
        if (array_key_exists($key, $this->map)) {
            return $this->map[$key];
        }
        return null;
    }

    public function setFunction(string $prefix, string $localName, \ReflectionMethod $method): void
    {
        $this->map[$prefix . ":" . $localName] = $method;
    }
}
