<?php

namespace Jabe\Test\Mock;

use Jabe\Delegate\VariableScopeInterface;
use Jabe\Impl\El\{
    JuelExpressionManager,
    VariableContextElResolver
};
use El\{
    ArrayELResolver,
    BeanELResolver,
    CompositeELResolver,
    ELResolver,
    ListELResolver,
    MapELResolver
};

class MockExpressionManager extends JuelExpressionManager
{
    protected function createElResolver(VariableScopeInterface $scope = null): ELResolver
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
