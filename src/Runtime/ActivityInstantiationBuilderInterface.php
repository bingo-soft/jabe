<?php

namespace Jabe\Runtime;

interface ActivityInstantiationBuilderInterface
{
    /**
     * If an instruction is submitted before then the variable is set when the
     * instruction is executed. Otherwise, the variable is set on the process
     * instance itself.
     */
    public function setVariable(?string $name, $value): ActivityInstantiationBuilderInterface;

    /**
     * If an instruction is submitted before then the local variable is set when
     * the instruction is executed. Otherwise, the variable is set on the process
     * instance itself.
     */
    public function setVariableLocal(?string $name, $value): ActivityInstantiationBuilderInterface;

    /**
     * If an instruction is submitted before then all variables are set when the
     * instruction is executed. Otherwise, the variables are set on the process
     * instance itself.
     */
    public function setVariables(array $variables): ActivityInstantiationBuilderInterface;

    /**
     * If an instruction is submitted before then all local variables are set when
     * the instruction is executed. Otherwise, the variables are set on the
     * process instance itself.
     */
    public function setVariablesLocal(array $variables): ActivityInstantiationBuilderInterface;
}
