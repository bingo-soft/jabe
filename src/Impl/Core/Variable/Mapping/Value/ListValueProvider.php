<?php

namespace Jabe\Impl\Core\Variable\Mapping\Value;

use Jabe\Delegate\VariableScopeInterface;

class ListValueProvider implements ParameterValueProviderInterface
{
    protected $providerList;

    public function __construct($providerList)
    {
        $this->providerList = $providerList;
    }

    public function getValue(?VariableScopeInterface $variableScope)
    {
        $valueList = [];
        foreach ($this->providerList as $provider) {
            $valueList[] = $provider->getValue($variableScope);
        }
        return $valueList;
    }

    public function getProviderList(): array
    {
        return $this->providerList;
    }

    public function setProviderList(array $providerList): void
    {
        $this->providerList = $providerList;
    }

    public function isDynamic(): bool
    {
        return true;
    }
}
