<?php

namespace Jabe\Engine\History;

interface SetRemovalTimeSelectModeForHistoricBatchesBuilderInterface extends SetRemovalTimeToHistoricBatchesBuilderInterface
{
    /**
     * Sets the removal time to an absolute date.
     *
     * @param removalTime supposed to be set to historic entities.
     * @return the builder.
     */
    public function absoluteRemovalTime(string $removalTime): SetRemovalTimeToHistoricBatchesBuilder;

    /**
     * Calculates the removal time dynamically based on the time to
     * live of the respective batch and the engine's removal time strategy.
     *
     * @return the builder.
     */
    public function calculatedRemovalTime(): SetRemovalTimeToHistoricBatchesBuilderInterface;

    /**
     * <p> Sets the removal time to {@code null}.
     *
     * @return the builder.
     */
    public function clearedRemovalTime(): SetRemovalTimeToHistoricBatchesBuilderInterface;
}
