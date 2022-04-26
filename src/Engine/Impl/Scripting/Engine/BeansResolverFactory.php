<?php

namespace Jabe\Engine\Impl\Scripting\Engine;

use Jabe\Engine\Delegate\VariableScopeInterface;
use Jabe\Engine\Impl\Context\Context;

class BeansResolverFactory implements ResolverFactoryInterface, ResolverInterface
{
    public function createResolver(?VariableScopeInterface $variableScope = null): ?ResolverInterface
    {
        return $this;
    }

    public function containsKey($key): bool
    {
        return array_key_exists($key, Context::getProcessEngineConfiguration()->getBeans());
    }

    public function get($key)
    {
        if ($this->containsKey($key)) {
            return Context::getProcessEngineConfiguration()->getBeans()[$key];
        }
        return null;
    }

    public function keySet(): array
    {
        return array_keys(Context::getProcessEngineConfiguration()->getBeans());
    }
}
