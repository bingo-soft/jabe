<?php

namespace BpmPlatform\Engine\Impl\Scripting\Engine;

use BpmPlatform\Engine\Delegate\VariableScopeInterface;

interface ResolverFactoryInterface
{
    public function createResolver(VariableScopeInterface $variableScope): ResolverInterface;
}
