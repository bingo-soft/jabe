<?php

namespace Tests\Bpmn\Event\Compensate\Helper;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    PhpDelegateInterface
};

class GetVariablesDelegate implements PhpDelegateInterface
{
    private $variable;
    public static $values = [];

    public function execute(DelegateExecutionInterface $execution)
    {
        fwrite(STDERR, "*** Hello world from GetVariablesDelegate.execute method ***\n");
        $variableName = $this->variable->getValue($execution);
        $value = $execution->getVariable($variableName);
        self::$values[] = $value;
    }
}
