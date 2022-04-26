<?php

namespace Jabe\Engine\Impl\Scripting\Engine;

use Jabe\Engine\Delegate\VariableScopeInterface;
use Jabe\Engine\Impl\Util\Scripting\BindingsInterface;

class ScriptBindingsFactory
{
    protected $resolverFactories;

    public function __construct(array $resolverFactories)
    {
        $this->resolverFactories = $resolverFactories;
    }

    public function createBindings(VariableScopeInterface $variableScope, BindingsInterface $engineBindings): BindingsInterface
    {
        $scriptResolvers = [];
        foreach ($this->resolverFactories as $scriptResolverFactory) {
            $resolver = $scriptResolverFactory->createResolver($variableScope);
            if ($resolver != null) {
                $scriptResolvers->add($resolver);
            }
        }
        return new ScriptBindings($scriptResolvers, $variableScope, $engineBindings);
    }

    public function getResolverFactories(): array
    {
        return $this->resolverFactories;
    }

    public function setResolverFactories(array $resolverFactories): void
    {
        $this->resolverFactories = $resolverFactories;
    }
}
