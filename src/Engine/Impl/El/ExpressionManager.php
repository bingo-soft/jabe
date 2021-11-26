<?php

namespace BpmPlatform\Engine\Impl\El;

use BpmPlatform\Engine\Delegate\VariableScopeInterface;
use BpmPlatform\Engine\Impl\Core\Variable\Scope\AbstractVariableScope;
use BpmPlatform\Engine\Impl\Util\El\{
    ArrayELResolver,
    CompositeELResolver,
    ELContext,
    ELResolver,
    ExpressionFactory,
    FunctionMapper,
    ListELResolver,
    MapELResolver,
    ValueExpression
};
use BpmPlatform\Engine\Impl\Juel\ExpressionFactoryImpl;
use BpmPlatform\Engine\Test\Mock\MockElResolver;
use BpmPlatform\Engine\Variable\Context\VariableContextInterface;

class ExpressionManager
{
    protected $functionMappers = [];
    protected $expressionFactory;
    // Default implementation (does nothing)
    protected $parsingElContext;
    protected $beans = [];
    protected $elResolver;

    public function __construct(?array $beans = [])
    {
        $this->parsingElContext = new ProcessEngineElContext($functionMappers);
        $this->expressionFactory = new ExpressionFactoryImpl();
        $this->beans = $beans;
    }

    public function createExpression(string $expression): ExpressionInterface
    {
        $valueExpression = $this->createValueExpression($expression);
        return new JuelExpression($valueExpression, $this, $expression);
    }

    public function createValueExpression(string $expression): ValueExpression
    {
        return $this->expressionFactory->createValueExpression($this->parsingElContext, $expression, "object");
    }

    public function setExpressionFactory(ExpressionFactory $expressionFactory): void
    {
        $this->expressionFactory = $expressionFactory;
    }

    public function getElContext(VariableScopeInterface $variableScope): ELContext
    {
        $elContext = null;
        if ($variableScope instanceof AbstractVariableScope) {
            $elContext = $variableScope->getCachedElContext();
        }

        if ($elContext == null) {
            $elContext = $this->createElContext($variableScope);
            if ($variableScope instanceof AbstractVariableScope) {
                $variableScope->setCachedElContext($elContext);
            }
        }

        return $elContext;
    }

    public function createElContext($variable): ?ELContext
    {
        $elResolver = $this->getCachedElResolver();
        $elContext = new ProcessEngineElContext($this->functionMappers, $elResolver);
        $elContext->putContext(ExpressionFactory::class, $expressionFactory);
        if ($variable instanceof VariableContextInterface) {
            $elContext->putContext(VariableContextInterface::class, $variable);
        } elseif ($variable instanceof VariableScopeInterface) {
            $elContext->putContext(VariableScopeInterface::class, $variable);
        }
        return $elContext;
    }

    protected function getCachedElResolver(): ELResolver
    {
        if ($this->elResolver == null) {
            $this->elResolver = $this->createElResolver();
        }
        return $this->elResolver;
    }

    protected function createElResolver(?VariableScopeInterface $scope = null): ELResolver
    {
        $elResolver = new CompositeELResolver();
        $elResolver->add(new VariableScopeElResolver());
        $elResolver->add(new VariableContextElResolver());
        $elResolver->add(new MockElResolver());

        if (!empty($this->beans)) {
            // ACT-1102: Also expose all beans in configuration when using standalone engine, not
            // in spring-context
            $elResolver->add(new ReadOnlyMapELResolver($this->beans));
        }

        $elResolver->add(new ProcessApplicationElResolverDelegate());

        $elResolver->add(new ArrayELResolver());
        $elResolver->add(new ListELResolver());
        $elResolver->add(new MapELResolver());
        $elResolver->add(new ProcessApplicationBeanElResolverDelegate());

        return $elResolver;
    }

    /**
     * @param elFunctionMapper
     */
    public function addFunctionMapper(FunctionMapper $elFunctionMapper): void
    {
        $this->functionMappers[] = $elFunctionMapper;
    }
}
