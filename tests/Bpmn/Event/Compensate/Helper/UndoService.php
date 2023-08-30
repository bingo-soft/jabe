<?php

namespace Tests\Bpmn\Event\Compensate\Helper;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    PhpDelegateInterface
};

class UndoService implements PhpDelegateInterface
{
    private $counterName;

    public function execute(DelegateExecutionInterface $execution)
    {
        echo "*** Hello world from UndoService.execute method ***\n";
        $variableName = $this->counterName->getValue($execution);
        $variable = $execution->getVariable($variableName);
        if ($variable === null) {
            $execution->setVariable($variableName, 1);
        } else {
            $execution->setVariable($variableName, intval($variable) + 1);
        }
    }
}
