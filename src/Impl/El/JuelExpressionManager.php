<?php

namespace Jabe\Impl\El;

use Jabe\Delegate\VariableScopeInterface;
use Jabe\Impl\Core\Variable\Scope\AbstractVariableScope;
use El\{
    ArrayELResolver,
    CompositeELResolver,
    ELContext,
    ELResolver,
    ExpressionFactory,
    FunctionMapper,
    ListELResolver,
    MapELResolver,
    ObjectELResolver,
    ValueExpression
};
use Juel\ExpressionFactoryImpl;
use Jabe\Impl\Util\EnsureUtil;
use Jabe\Test\Mock\MockElResolver;
use Jabe\Variable\Context\VariableContextInterface;

class JuelExpressionManager implements ExpressionManagerInterface, ElProviderCompatibleInterface
{
    protected $functions = [];
    protected $expressionFactory;
    protected $beans = [];
    protected bool $initialized = false;
    protected $elResolver;
    protected $functionMapper;
    // Default implementation (does nothing)
    protected $parsingElContext;
    protected $elProvider;

    public function __construct(array $beans = [])
    {
        // Use the ExpressionFactoryImpl built-in version of juel, with parametrised
        // method expressions enabled
        $this->expressionFactory = new ExpressionFactoryImpl();
        $this->beans = $beans;
    }

    public function createExpression(?string $expression): ExpressionInterface
    {
        $this->ensureInitialized();
        $valueExpression = $this->createValueExpression($expression);
        return new JuelExpression($valueExpression, $this, $expression);
    }

    public function addFunction(?string $name, \ReflectionMethod $function): void
    {
        EnsureUtil::ensureNotEmpty("name", "name", $name);
        EnsureUtil::ensureNotNull("function", "function", $function);
        $this->functions[$name] = $function;
    }

    public function createValueExpression(?string $expression): ValueExpression
    {
        $this->ensureInitialized();
        return $this->expressionFactory->createValueExpression($this->parsingElContext, $expression, null, "object");
    }

    public function setExpressionFactory(ExpressionFactory $expressionFactory): void
    {
        $this->expressionFactory = $expressionFactory;
    }

    public function getElContext(VariableScopeInterface $variableScope): ELContext
    {
        $this->ensureInitialized();
        $elContext = null;
        if ($variableScope instanceof AbstractVariableScopeInterface) {
            $variableScopeImpl = $variableScope;
            $elContext = $variableScopeImpl->getCachedElContext();
        }

        if ($elContext === null) {
            $elContext = $this->createElContext($variableScope);
            if ($variableScope instanceof AbstractVariableScopeInterface) {
                $variableScope->setCachedElContext($elContext);
            }
        }

        return $elContext;
    }

    protected function createElContext(/*VariableScopeInterface|VariableContextInterface*/$variableScopeOrContext): ProcessEngineElContext
    {
        if ($variableScopeOrContext instanceof VariableScopeInterface) {
            $this->ensureInitialized();
            $elContext = new ProcessEngineElContext($this->functionMapper, $this->elResolver);
            $elContext->putContext(ExpressionFactory::class, $this->expressionFactory);
            $elContext->putContext(VariableScopeInterface::class, $variableScopeOrContext);
            return $elContext;
        } elseif ($variableScopeOrContext instanceof VariableContextInterface) {
            $this->ensureInitialized();
            $elContext = new ProcessEngineElContext($this->functionMapper, $this->elResolver);
            $elContext->putContext(ExpressionFactory::class, $this->expressionFactory);
            $elContext->putContext(VariableContextInterface::class, $variableScopeOrContext);
            return $elContext;
        }
    }

    protected function ensureInitialized(): void
    {
        if (!$this->initialized) {
            $this->elResolver = $this->createElResolver();
            $this->functionMapper = $this->createFunctionMapper();
            $this->parsingElContext = new ProcessEngineElContext($this->functionMapper);
            $this->initialized = true;
        }
    }

    protected function createElResolver(VariableScopeInterface $scope = null): ELResolver
    {
        $elResolver = new CompositeELResolver();
        $elResolver->add(new VariableScopeElResolver());
        $elResolver->add(new VariableContextElResolver());
        $elResolver->add(new MockElResolver());

        /*if (!empty($this->beans)) {
            // ACT-1102: Also expose all beans in configuration when using standalone
            // engine, not
            // in spring-context
            elResolver.add(new ReadOnlyMapELResolver(beans));
        }*/

        //$elResolver->add(new ProcessApplicationElResolverDelegate());
        $elResolver->add(new ArrayELResolver());
        $elResolver->add(new ObjectELResolver());
        //$elResolver->add(new ListELResolver());
        //$elResolver->add(new MapELResolver());
        //$elResolver->add(new ProcessApplicationBeanElResolverDelegate());

        return $elResolver;
    }

    protected function createFunctionMapper(): FunctionMapper
    {
        $functions = $this->functions;
        $functionMapper = new class ($functions) extends FunctionMapper {
            private $functions;

            public function __construct(array $functions)
            {
                $this->functions = $functions;
            }

            public function resolveFunction(?string $prefix, ?string $localName)
            {
                if (array_key_exists($localName, $this->functions)) {
                    return $this->functions[$localName];
                } elseif (function_exists($localName)) {
                    return new \ReflectionFunction($localName);
                }
            }
        };
        return $functionMapper;
    }

    public function toElProvider()
    {
        if ($this->elProvider == null) {
            $this->elProvider = $this->createElProvider();
        }
        return $this->elProvider;
    }

    protected function createElProvider()
    {
        return new ProcessEngineJuelElProvider($this);
    }
}
