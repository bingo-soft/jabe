<?php

namespace Jabe\Engine\Impl\Metrics\Util;

use Jabe\Engine\Management\Metrics;

class MetricsUtil
{
    /**
     * Resolves the internal name of the metric by the public name.
     *
     * @param publicName the public name
     * @return string the internal name
     */
    public static function resolveInternalName(?string $publicName): ?string
    {
        if ($publicName == null) {
            return null;
        }
        switch ($publicName) {
            case Metrics::TASK_USERS:
                return Metrics::UNIQUE_TASK_WORKERS;
            case Metrics::PROCESS_INSTANCES:
                return Metrics::ROOT_PROCESS_INSTANCE_START;
            //case Metrics::DECISION_INSTANCES:
            //    return Metrics::EXECUTED_DECISION_INSTANCES;
            case Metrics::FLOW_NODE_INSTANCES:
                return Metrics::ACTIVTY_INSTANCE_START;
            default:
                return $publicName;
        }
    }

    /**
     * Resolves the public name of the metric by the internal name.
     *
     * @param internalName the internal name
     * @return string the public name
     */
    public static function resolvePublicName(?string $internalName): ?string
    {
        if ($internalName == null) {
            return null;
        }
        switch ($internalName) {
            case Metrics::UNIQUE_TASK_WORKERS:
                return Metrics::TASK_USERS;
            case Metrics::ROOT_PROCESS_INSTANCE_START:
                return Metrics::PROCESS_INSTANCES;
            //case Metrics::EXECUTED_DECISION_INSTANCES:
            //    return Metrics::DECISION_INSTANCES;
            case Metrics::ACTIVTY_INSTANCE_START:
                return Metrics::FLOW_NODE_INSTANCES;
            default:
                return $internalName;
        }
    }
}
