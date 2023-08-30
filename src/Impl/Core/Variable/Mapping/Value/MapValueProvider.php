<?php

namespace Jabe\Impl\Core\Variable\Mapping\Value;

use Jabe\Delegate\VariableScopeInterface;

class MapValueProvider implements ParameterValueProviderInterface
{
    protected $providerMap;

    public function __construct($providerMap)
    {
        $this->providerMap = $providerMap;
    }

    public function getValue(?VariableScopeInterface $variableScope)
    {
        $valueMap = [];
        foreach ($this->providerMap as $pair) {
            $valueMap[] = [$pair[0]->getValue($variableScope), $pair[1]->getValue($variableScope)];
        }
        return $valueMap;
    }

    public function getProviderMap(): array
    {
        return $this->providerMap;
    }

    public function setProviderMap(array $providerMap): void
    {
        $this->providerMap = $providerMap;
    }

    public function isDynamic(): bool
    {
        return true;
    }
}
