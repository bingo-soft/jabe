<?php

namespace Jabe\Impl\Scripting\Engine;

use Jabe\Delegate\VariableScopeInterface;

interface ResolverFactoryInterface
{
    public function createResolver(?VariableScopeInterface $variableScope = null): ?ResolverInterface;
}
