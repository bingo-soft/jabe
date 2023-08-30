<?php

namespace Jabe\Delegate;

use Jabe\Variable\VariableMapInterface;

interface DelegateVariableMappingInterface
{
    /**
     * Maps the input variables into the given variables map.
     * The variables map will be used by the sub process.
     *
     * @param superExecution the execution object of the super (outer) process
     * @param subVariables the variables map of the sub (inner) process
     */
    public function mapInputVariables(DelegateExecutionInterface $superExecution, VariableMapInterface $subVariables): void;

    /**
     * Maps the output variables into the outer process. This means the variables of
     * the sub process, which can be accessed via the subInstance, will be
     * set as variables into the super process, for example via ${superExecution.setVariables}.
     *
     * @param superExecution the execution object of the super (outer) process, which gets the output variables
     * @param subInstance the instance of the sub process, which contains the variables
     */
    public function mapOutputVariables(DelegateExecutionInterface $superExecution, VariableScopeInterface $subInstance): void;
}
