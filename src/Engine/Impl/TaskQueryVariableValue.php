<?php

namespace Jabe\Engine\Impl;

class TaskQueryVariableValue extends QueryVariableValue
{
    protected $isProcessInstanceVariable;

    /**
     * <p>The parameters <code>isTaskVariable</code> and <code> isProcessInstanceVariable</code>
     * have the following meaning:</p>
     *
     * <ul>
     *   <li>if <code>isTaskVariable == true</code>: only query after task variables</li>
     *   <li>if <code>isTaskVariable == false && isProcessInstanceVariable == true</code>:
     *       only query after process instance variables</li>
     *   <li>if <code>isTaskVariable == false && isProcessInstanceVariable == false</code>:
     *       only query after case instance variables</li>
     * </ul>
     */

    public function __construct(string $name, $value, string $operator, bool $isTaskVariable, bool $isProcessInstanceVariable, bool $variableNameIgnoreCase = false, bool $variableValueIgnoreCase = false)
    {
        parent::__construct($name, $value, $operator, $isTaskVariable, $variableNameIgnoreCase, $variableValueIgnoreCase);
        $this->isProcessInstanceVariable = $isProcessInstanceVariable;
    }

    public function isProcessInstanceVariable(): bool
    {
        return $this->isProcessInstanceVariable;
    }
}
