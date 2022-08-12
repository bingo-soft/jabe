<?php

namespace Jabe\Impl\El;

use El\{
    ELContext,
    ELResolver,
    FunctionMapper,
    VariableMapper
};

class ProcessEngineElContext extends ELContext
{
    protected $elResolver;

    protected $functionMapper;

    public function __construct(FunctionMapper $functionMapper, ELResolver $elResolver = null)
    {
        $this->functionMapper = $functionMapper;
        $this->elResolver = $elResolver;
    }

    public function getELResolver(): ELResolver
    {
        return $this->elResolver;
    }

    public function getFunctionMapper(): FunctionMapper
    {
        return $this->functionMapper;
    }

    public function getVariableMapper(): VariableMapper
    {
        return null;
    }
}
