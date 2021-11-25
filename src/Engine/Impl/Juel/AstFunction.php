<?php

namespace BpmPlatform\Engine\Impl\Juel;

use BpmPlatform\Engine\Impl\Util\El\{
    ELContext,
    ELException
};

class AstFunction extends AstRightValue implements FunctionNode
{
    private $index;
    private $name;
    private $params;
    private $varargs;

    public function __construct(string $name, int $index, AstParameters $params, bool $varargs = false)
    {
        $this->name = $name;
        $this->index = $index;
        $this->params = $params;
        $this->varargs = $varargs;
    }

    /**
     * Invoke method.
     * @param bindings
     * @param context
     * @param base
     * @param method
     * @return method result
     * @throws InvocationTargetException
     * @throws IllegalAccessException
     */
    public function invoke(Bindings $bindings, ELContext $context, $base, \ReflectionMethod $method)
    {
        $parameters = $method->getParameters();
        $types = [];
        if (!empty($parameters)) {
            foreach ($parameters as $param) {
                $type = $param->getType();
                if ($type != null) {
                    $types[] = $type->getName();
                } else {
                    $types[] = "undefined";
                }
            }
        }
        $params = [];
        for ($i = 0; $i < count($parameters); $i++) {
            $param = $this->getParam($i)->eval($bindings, $context);
            if ($param != null && $types[$i] != "undefined") {
                $params[$i] = $bindings->convert($param, $types[$i]);
            } else {
                $params[$i] = $param;
            }
        }
        return $method->invoke($base, ...$params);
    }

    public function eval(Bindings $bindings, ELContext $context)
    {
        $method = $bindings->getFunction($this->index);
        try {
            return $this->invoke($bindings, $context, null, $method);
        } catch (\Exception $e) {
            throw new ELException(LocalMessages::get("error.function.invocation", $this->name));
        }
    }

    public function __toString()
    {
        return $this->name;
    }

    public function appendStructure(string &$b, Bindings $bindings): void
    {
        $b .= $bindings != null && $bindings->isFunctionBound($this->index) ? "<fn>" : $this->name;
        $this->params->appendStructure($b, $bindings);
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isVarArgs(): bool
    {
        return $this->varargs;
    }

    public function getParamCount(): int
    {
        return $this->params->getCardinality();
    }

    protected function getParam(int $i): ?AstNode
    {
        return $this->params->getChild($i);
    }

    public function getCardinality(): int
    {
        return 1;
    }

    public function getChild(int $i): ?AstNode
    {
        return $i == 0 ? $this->params : null;
    }
}
