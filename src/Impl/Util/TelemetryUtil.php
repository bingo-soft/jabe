<?php

namespace Jabe\Impl\Util;

use Jabe\Impl\Metrics\MetricsRegistry;
use Jabe\Impl\Telemetry\TelemetryRegistry;

class TelemetryUtil
{
    public static function toggleLocalTelemetry(
        bool $telemetryActivated,
        TelemetryRegistry $telemetryRegistry,
        MetricsRegistry $metricsRegistry
    ): void {
        $previouslyActivated = $telemetryRegistry->setTelemetryLocallyActivated($telemetryActivated);

        if (!$previouslyActivated && $telemetryActivated) {
            if ($telemetryRegistry !== null) {
                $telemetryRegistry->clearCommandCounts();
            }
            if ($metricsRegistry !== null) {
                $metricsRegistry->clearTelemetryMetrics();
            }
        }
    }
}
