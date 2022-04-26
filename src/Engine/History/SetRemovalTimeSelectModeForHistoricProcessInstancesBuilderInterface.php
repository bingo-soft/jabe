<?php

namespace Jabe\Engine\History;

interface SetRemovalTimeSelectModeForHistoricProcessInstancesBuilderInterface extends SetRemovalTimeToHistoricBatchesBuilderInterface
{
    /**
     * Sets the removal time to an absolute date.
     *
     * @param removalTime supposed to be set to historic entities.
     * @return the builder.
     */
    public function absoluteRemovalTime(string $removalTime): SetRemovalTimeToHistoricProcessInstancesBuilderInterface;

    /**
     * <p> Calculates the removal time dynamically based on the respective process definition time to
     * live and the process engine's removal time strategy.
     *
     * <p> In case {@link #hierarchical()} is enabled, the removal time is being calculated
     * based on the base time and time to live of the historic root process instance.
     *
     * @return the builder.
     */
    public function calculatedRemovalTime(): SetRemovalTimeToHistoricProcessInstancesBuilderInterface;

    /**
     * <p> Sets the removal time to {@code null}.
     *
     * @return the builder.
     */
    public function clearedRemovalTime(): SetRemovalTimeToHistoricProcessInstancesBuilderInterface;
}
