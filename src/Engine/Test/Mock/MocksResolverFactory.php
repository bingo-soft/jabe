<?php

namespace Jabe\Engine\Test\Mock;

use Jabe\Engine\Delegate\VariableScopeInterface;
use Jabe\Engine\Impl\Scripting\Engine\{
    ResolverInterface,
    ResolverFactoryInterface
};

class MocksResolverFactory implements ResolverFactoryInterface, ResolverInterface
{
    public function createResolver(?VariableScopeInterface $variableScope = null): ?ResolverInterface
    {
        return $this;
    }

    public function containsKey($key): bool
    {
        return Mocks::get($key) !== null;
    }

    public function get($key)
    {
        return Mocks::get($key);
    }

    public function keySet(): array
    {
        return array_keys(Mocks::getMocks());
    }
}
