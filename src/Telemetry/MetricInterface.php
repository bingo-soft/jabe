<?php

namespace Jabe\Telemetry;

interface MetricInterface
{
    /**
     * The count of this metric i.e., how often did the engine perform the action
     * associated with this metric.
     */
    public function getCount(): int;
}
