<?php

namespace Tests\Bpmn\Event\Compensate\Helper;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    PhpDelegateInterface
};

class SetVariablesDelegate implements PhpDelegateInterface
{
    private $variable;
    public static $values = [];
    public static $cnt = 0;
    private static $execution;

    public function execute(DelegateExecutionInterface $execution)
    {
        fwrite(STDERR, "*** Hello world from SetVariablesDelegate.execute method ***\n");
        $variableName = $this->variable->getValue($execution);
        $value = array_shift(self::$values);
        $execution->setVariableLocal($variableName, $value);
        self::$execution = $execution;
    }

    public static function setValues(array $values): void
    {
        fwrite(STDERR, "*** Hello world from SetVariablesDelegate.setValues method ***\n");
        self::$values = $values;
    }
}
