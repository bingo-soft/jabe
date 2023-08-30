<?php

namespace Jabe\Management;

interface MetricIntervalValueInterface
{
    /**
     * Returns the name of the metric.
     *
     * @see constants in Metrics for a list of names which can be returned here
     *
     * @return string the name of the metric
     */
    public function getName(): ?string;

    /**
     * Returns
     *        the reporter name of the metric, identifies the node which generates this metric.
     *        'null' when the metrics are aggregated by reporter.
     *
     * @return string the reporter name
     */
    public function getReporter(): ?string;

    /**
     * Returns the timestamp as date object, on which the metric was created.
     *
     * @return string the timestamp
     */
    public function getTimestamp(): ?string;

    /**
     * Returns the value of the metric.
     *
     * @return int the value
     */
    public function getValue(): int;
}
