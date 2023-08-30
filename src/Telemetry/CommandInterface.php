<?php

namespace Jabe\Telemetry;

interface CommandInterface
{
    /**
     * The count of this command i.e., how often did the engine engine execute
     * this command.
     */
    public function getCount(): int;
}
