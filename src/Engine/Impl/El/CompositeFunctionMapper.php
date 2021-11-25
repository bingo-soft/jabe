<?php

namespace BpmPlatform\Engine\Impl\El;

use BpmPlatform\Engine\Impl\Util\El\FunctionMapper;

class CompositeFunctionMapper extends FunctionMapper
{
    protected $delegateMappers = [];

    public function __construct(array $delegateMappers)
    {
        $this->delegateMappers = $delegateMappers;
    }

    public function resolveFunction(string $prefix, string $localName): ?\ReflectionMethod
    {
        foreach ($this->delegateMappers as $mapper) {
            $resolvedFunction = $mapper->resolveFunction($prefix, $localName);
            if ($resolvedFunction != null) {
                return $resolvedFunction;
            }
        }
        return null;
    }
}
