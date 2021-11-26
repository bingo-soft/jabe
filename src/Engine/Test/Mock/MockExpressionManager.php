<?php

namespace BpmPlatform\Engine\Test\Mock;

use BpmPlatform\Engine\Delegate\VariableScopeInterface;
use BpmPlatform\Engine\Impl\El\{
    ExpressionManager,
    VariableContextElResolver
};
use BpmPlatform\Engine\Impl\Util\El\{
    ArrayELResolver,
    BeanELResolver,
    CompositeELResolver,
    ELResolver,
    ListELResolver,
    MapELResolver
};

class MockExpressionManager extends ExpressionManager
{
    protected function createElResolver(?VariableScopeInterface $scope = null): ELResolver
    {
        $compositeElResolver = new CompositeELResolver();
        $compositeElResolver->add(new VariableScopeElResolver());
        $compositeElResolver->add(new VariableContextElResolver());
        $compositeElResolver->add(new MockElResolver());
        $compositeElResolver->add(new ArrayELResolver());
        $compositeElResolver->add(new ListELResolver());
        $compositeElResolver->add(new MapELResolver());
        $compositeElResolver->add(new BeanELResolver());
        return $compositeElResolver;
    }
}
