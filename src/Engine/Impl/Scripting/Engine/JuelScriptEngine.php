<?php

namespace BpmPlatform\Engine\Impl\Scripting\Engine;

use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\Impl\El\ExpressionFactoryResolver;
use BpmPlatform\Engine\Impl\Util\El\{
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
use BpmPlatform\Engine\Impl\Juel\SimpleResolver;
use BpmPlatform\Engine\Impl\Util\ReflectUtil;
use BpmPlatform\Engine\Impl\Scripting\{
    ScriptEngineFactoryInterface,
    ScriptEngineInterface
};

class JuelScriptEngine implements ScriptEngineInterface
{

}
