<?php

namespace Tests\Bpmn\Event\Compensate\Helper;

use Jabe\Delegate\{
    DelegateExecutionInterface,
    PhpDelegateInterface
};

class BookFlightService implements PhpDelegateInterface
{
    public static $bookedFlights = [];

    public function execute(DelegateExecutionInterface $execution)
    {
        $flight = $execution->getVariable("flight");

        if ($flight !== null) {
            self::$bookedFlights[] = $flight;
        }
    }
}
