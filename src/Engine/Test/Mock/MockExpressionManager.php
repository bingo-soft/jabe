<?php

namespace Jabe\Engine\Test\Mock;

use Jabe\Engine\Delegate\VariableScopeInterface;
use Jabe\Engine\Impl\El\{
    ExpressionManager,
    VariableContextElResolver
};
use Jabe\Engine\Impl\Util\El\{
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
