<?php

namespace Jabe\Impl\Scripting\Engine;

use Jabe\Delegate\VariableScopeInterface;

class VariableScopeResolverFactory implements ResolverFactoryInterface
{
    public function createResolver(?VariableScopeInterface $variableScope = null): ?ResolverInterface
    {
        if ($variableScope !== null) {
            return new VariableScopeResolver($variableScope);
        }
        return null;
    }
}
