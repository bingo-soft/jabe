<?php

namespace BpmPlatform\Engine\Impl\Scripting\Engine;

use BpmPlatform\Engine\Delegate\VariableScopeInterface;
use BpmPlatform\Engine\Impl\Context\Context;

class BeansResolverFactory implements ResolverFactoryInterface, ResolverInterface
{
    public function createResolver(VariableScopeInterface $variableScope): ResolverInterface
    {
        return $this;
    }

    public function containsKey($key): bool
    {
        return array_key_exists($key, Context::getProcessEngineConfiguration()->getBeans());
    }

    public function get($key)
    {
        return Context::getProcessEngineConfiguration()->getBeans()[$key];
    }

    public function keySet(): array
    {
        return array_keys(Context::getProcessEngineConfiguration()->getBeans());
    }
}
