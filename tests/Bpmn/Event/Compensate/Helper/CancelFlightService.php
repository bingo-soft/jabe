<?php

namespace Tests\Bpmn\Event\Compensate\Helper;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    PhpDelegateInterface
};

class CancelFlightService implements PhpDelegateInterface
{
    public static $bookedFlights = [];

    public function execute(DelegateExecutionInterface $execution)
    {
        fwrite(STDERR, "*** Hello world from CancelFlightService.execute method ***\n");
        $flight = $execution->getVariable("flight");

        if ($flight !== null) {
            self::$bookedFlights[] = $flight;
        }
    }
}
