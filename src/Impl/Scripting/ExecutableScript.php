<?php

namespace Jabe\Impl\Scripting;

use Script\{
    BindingsInterface,
    ScriptEngineInterface
};
use Jabe\ProcessEngineException;
use Jabe\Delegate\{
    //DelegateCaseExecutionInterface,
    DelegateExecutionInterface,
    VariableScopeInterface
};
use Jabe\Impl\Persistence\Entity\TaskEntity;

abstract class ExecutableScript
{
    /** The language of the script. Used to resolve the
     * ScriptEngine. */
    protected $language;

    public function __construct(string $language)
    {
        $this->language = $language;
    }

    /**
     * The language in which the script is written.
     * @return string the language
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * <p>Evaluates the script using the provided engine and bindings</p>
     *
     * @param scriptEngine the script engine to use for evaluating the script.
     * @param variableScope the variable scope of the execution
     * @param bindings the bindings to use for evaluating the script.
     * @throws ProcessEngineException in case the script cannot be evaluated.
     * @return mixed the result of the script evaluation
     */
    public function execute(ScriptEngineInterface $scriptEngine, VariableScopeInterface $variableScope, BindingsInterface $bindings)
    {
        return $this->evaluate($scriptEngine, $variableScope, $bindings);
    }

    protected function getActivityIdExceptionMessage(VariableScopeInterface $variableScope): string
    {
        $activityId = null;
        $definitionIdMessage = "";

        if ($variableScope instanceof DelegateExecutionInterface) {
            $activityId = $variableScope->getCurrentActivityId();
            $definitionIdMessage = " in the process definition with id '" . $variableScope->getProcessDefinitionId() . "'";
        } elseif ($variableScope instanceof TaskEntity) {
            $task = $variableScope;
            if ($task->getExecution() !== null) {
                $activityId = $task->getExecution()->getActivityId();
                $definitionIdMessage = " in the process definition with id '" . $task->getProcessDefinitionId() . "'";
            }
            /*if ($task.getCaseExecution() !== null) {
                activityId = task.getCaseExecution().getActivityId();
                definitionIdMessage = " in the case definition with id '" + task.getCaseDefinitionId() + "'";
            }*/
        }/* elseif ($variableScope instanceof DelegateCaseExecution) {
            activityId = ((DelegateCaseExecution) variableScope).getActivityId();
            definitionIdMessage = " in the case definition with id '" + ((DelegateCaseExecution) variableScope).getCaseDefinitionId() + "'";
        }*/

        if ($activityId === null) {
            return "";
        } else {
            return " while executing activity '" . $activityId . "'" . $definitionIdMessage;
        }
    }

    abstract public function evaluate(ScriptEngineInterface $scriptEngine, VariableScopeInterface $variableScope, BindingsInterface $bindings);
}
