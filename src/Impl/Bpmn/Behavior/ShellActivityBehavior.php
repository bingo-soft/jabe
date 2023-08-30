<?php

namespace Jabe\Impl\Bpmn\Behavior;

use Symfony\Component\Process\Process;
use Jabe\Delegate\{
    DelegateExecutionInterface,
    ExpressionInterface
};
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Pvm\Delegate\ActivityExecutionInterface;

class ShellActivityBehavior extends AbstractBpmnActivityBehavior
{
    //protected static final BpmnBehaviorLogger LOG = ProcessEngineLogger.BPMN_BEHAVIOR_LOGGER;

    protected $command;
    protected $wait;
    protected $arg1;
    protected $arg2;
    protected $arg3;
    protected $arg4;
    protected $arg5;
    protected $outputVariable;
    protected $errorCodeVariable;
    protected $redirectError;
    protected $cleanEnv;
    protected $directory;

    private $commandStr;
    private $arg1Str;
    private $arg2Str;
    private $arg3Str;
    private $arg4Str;
    private $arg5Str;
    private $waitStr;
    private $resultVariableStr;
    private $errorCodeVariableStr;
    private $waitFlag;
    private $redirectErrorFlag;
    private $cleanEnvBoolan;
    private $directoryStr;

    public function __construct()
    {
        parent::__construct();
    }

    private function readFields(ActivityExecutionInterface $execution): void
    {
        $this->commandStr = $this->getStringFromField($this->command, $execution);
        $this->arg1Str = $this->getStringFromField($this->arg1, $execution);
        $this->arg2Str = $this->getStringFromField($this->arg2, $execution);
        $this->arg3Str = $this->getStringFromField($this->arg3, $execution);
        $this->arg4Str = $this->getStringFromField($this->arg4, $execution);
        $this->arg5Str = $this->getStringFromField($this->arg5, $execution);
        $this->waitStr = $this->getStringFromField($this->wait, $execution);
        $this->resultVariableStr = $this->getStringFromField($this->outputVariable, $execution);
        $this->errorCodeVariableStr = $this->getStringFromField($this->errorCodeVariable, $execution);

        $redirectErrorStr = $this->getStringFromField($this->redirectError, $execution);
        $cleanEnvStr = $this->getStringFromField($this->cleanEnv, $execution);

        $this->waitFlag = $this->waitStr === null || $this->waitStr == "true";
        $this->redirectErrorFlag = $redirectErrorStr !== null && $redirectErrorStr == "true";
        $this->cleanEnvBoolan = $cleanEnvStr !== null && $cleanEnvStr == "true";
        $this->directoryStr = $this->getStringFromField($this->directory, $execution);
    }

    public function execute(/*ActivityExecutionInterface*/$execution): void
    {
        $this->readFields($execution);

        $argList = [];
        $argList[] = $this->commandStr;

        if ($this->arg1Str !== null) {
            $argList[] = $this->arg1Str;
        }
        if ($this->arg2Str !== null) {
            $argList[] = $this->arg2Str;
        }
        if ($this->arg3Str !== null) {
            $argList[] = $this->arg3Str;
        }
        if ($this->arg4Str !== null) {
            $argList[] = $this->arg4Str;
        }
        if ($this->arg5Str !== null) {
            $argList[] = $this->arg5Str;
        }

        $process = new Process($argList);

        try {
            $process->start();

            if ($this->waitFlag) {
                $errorCode = $process->wait();

                if ($this->resultVariableStr !== null) {
                    $result = $process->getOutput();
                    $execution->setVariable($this->resultVariableStr, $result);
                }

                if ($this->errorCodeVariableStr !== null) {
                    $execution->setVariable($this->errorCodeVariableStr, intval($errorCode));
                }
            }
        } catch (\Exception $e) {
            //throw LOG.shellExecutionException(e);
            throw $e;
        }

        $this->leave($execution);
    }

    protected function getStringFromField(?ExpressionInterface $expression, DelegateExecutionInterface $execution): ?string
    {
        if ($expression !== null) {
            $value = $expression->getValue($execution);
            if ($value !== null) {
                return $value;
            }
        }
        return null;
    }
}
