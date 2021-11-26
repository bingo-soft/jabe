<?php

namespace BpmPlatform\Engine\Impl\Scripting\Engine;

use BpmPlatform\Engine\Delegate\VariableScopeInterface;

class VariableScopeResolverFactory implements ResolverFactoryInterface
{
    public function createResolver(?VariableScopeInterface $variableScope = null): ?ResolverInterface
    {
        if ($variableScope != null) {
            return new VariableScopeResolver($variableScope);
        }
        return null;
    }
}
