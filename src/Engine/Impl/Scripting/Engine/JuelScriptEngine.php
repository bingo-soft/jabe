<?php

namespace Jabe\Engine\Impl\Scripting\Engine;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\El\ExpressionFactoryResolver;
use Jabe\Engine\Impl\Util\El\{
    ArrayELResolver,
    BeanELResolver,
    CompositeELResolver,
    ELContext,
    ELException,
    ELResolver,
    ExpressionFactory,
    FunctionMapper,
    ListELResolver,
    MapELResolver,
    ValueExpression,
    VariableMapper
};
use Jabe\Engine\Impl\Juel\SimpleResolver;
use Jabe\Engine\Impl\Util\ReflectUtil;
use Jabe\Engine\Impl\Util\Scripting\{
    AbstractScriptEngine,
    BindingsInterface,
    CompiledScript,
    ScriptContextInterface,
    ScriptEngineInterface,
    ScriptEngineFactoryInterface,
    ScriptException,
    SimpleBindings
};

class JuelScriptEngine extends AbstractScriptEngine
{
    private $scriptEngineFactory;
    private $expressionFactory;

    public function __construct(?ScriptEngineFactoryInterface $scriptEngineFactory = null)
    {
        $this->scriptEngineFactory = $scriptEngineFactory;
        // Resolve the ExpressionFactory
        $this->expressionFactory = ExpressionFactoryResolver::resolveExpressionFactory();
    }

    public function eval(string $script, ?ScriptContextInterface $scriptContext = null, ?BindingsInterface $bindings = null)
    {
        if ($scriptContext === null && $bindings !== null) {
            $scriptContext = $this->getScriptContext($bindings);
        }
        $expr = $this->parse($script, $scriptContext);
        return $this->evaluateExpression($expr, $scriptContext);
    }

    public function getFactory(): ScriptEngineFactoryInterface
    {
        if ($this->scriptEngineFactory === null) {
            $this->scriptEngineFactory = new JuelScriptEngineFactory();
        }
        return $this->scriptEngineFactory;
    }

    public function createBindings(): BindingsInterface
    {
        return new SimpleBindings();
    }

    private function evaluateExpression(ValueExpression $expr, ScriptContextInterface $ctx)
    {
        try {
            return $expr->getValue($this->createElContext($ctx));
        } catch (ELException $elexp) {
            throw new ScriptException($elexp);
        }
    }

    public function createElResolver(): ELResolver
    {
        $compositeResolver = new CompositeELResolver();
        $compositeResolver->add(new ArrayELResolver());
        $compositeResolver->add(new ListELResolver());
        $compositeResolver->add(new MapELResolver());
        $compositeResolver->add(new BeanELResolver());
        return new SimpleResolver($compositeResolver);
    }

    private function parse(string $script, ScriptContextInterface $scriptContext): ValueExpression
    {
        try {
            return $this->expressionFactory->createValueExpression($this->createElContext($scriptContext), $script, "object");
        } catch (ELException $ele) {
            throw new ScriptException($ele);
        }
    }

    private function createElContext(ScriptContextInterface $scriptCtx): ELContext
    {
        // Check if the ELContext is already stored on the ScriptContext
        $existingELCtx = $scriptCtx->getAttribute("elcontext");
        if ($existingELCtx instanceof ELContext) {
            return $existingELCtx;
        }

        $scriptCtx->setAttribute("context", $scriptCtx, ScriptContextInterface::ENGINE_SCOPE);

        // Built-in function are added to ScriptCtx
        // $scriptCtx->setAttribute("out:print", $this->getPrintMethod(), ScriptContextInterface::ENGINE_SCOPE);
        // $scriptCtx->setAttribute("lang:import", $this->getImportMethod(), ScriptContextInterface::ENGINE_SCOPE);

        $elContext = new class ($this, $scriptCtx) extends ELContext {

            private $resolver;
            private $varMapper;
            private $funcMapper;

            public function __construct(ScriptEngineInterface $engine, ScriptContextInterface $scriptCtx)
            {
                $this->resolver = $engine->createElResolver();
                $this->varMapper = new ScriptContextVariableMapper($scriptCtx);
                $this->funcMapper = new ScriptContextFunctionMapper($scriptCtx);
            }

            public function getELResolver(): ?ELResolver
            {
                return $this->resolver;
            }

            public function getVariableMapper(): ?VariableMapper
            {
                return $this->varMapper;
            }

            public function getFunctionMapper(): ?FunctionMapper
            {
                return $this->funcMapper;
            }
        };
        // Store the elcontext in the scriptContext to be able to reuse
        $scriptCtx->setAttribute("elcontext", $elContext, ScriptContextInterface::ENGINE_SCOPE);
        return $elContext;
    }
}
