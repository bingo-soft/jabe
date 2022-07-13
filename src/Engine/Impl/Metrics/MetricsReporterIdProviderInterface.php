<?php

namespace Jabe\Engine\Impl\Metrics;

use Jabe\Engine\ProcessEngineInterface;

interface MetricsReporterIdProviderInterface
{
    /**
     * Provides an id that identifies the metrics reported as part of the given engine's
     * process execution. May return null.
     */
    public function provideId(ProcessEngineInterface $processEngine): string;
}
