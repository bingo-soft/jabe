<?php

namespace Jabe\Engine\Impl\Scripting\Engine;

use Jabe\Engine\Delegate\VariableScopeInterface;

interface ResolverFactoryInterface
{
    public function createResolver(?VariableScopeInterface $variableScope = null): ?ResolverInterface;
}
