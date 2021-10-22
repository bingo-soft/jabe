<?php

namespace BpmPlatform\Engine\History;

use BpmPlatform\Engine\Batch\BatchInterface;

interface SetRemovalTimeToHistoricProcessInstancesBuilderInterface extends SetRemovalTimeToHistoricBatchesBuilderInterface
{
    /**
     * Selects historic process instances by the given query.
     *
     * @param historicProcessInstanceQuery to be evaluated.
     * @return the builder.
     */
    public function byQuery(HistoricProcessInstanceQueryInterface $historicProcessInstanceQuery): SetRemovalTimeToHistoricProcessInstancesBuilderInterface;

    /**
     * Selects historic decision instances by the given ids.
     *
     * @param historicProcessInstanceIds supposed to be affected.
     * @return the builder.
     */
    public function byIds(array $historicProcessInstanceIds): SetRemovalTimeToHistoricProcessInstancesBuilderInterface;

    /**
     * Takes additionally those historic process instances into account that are part of
     * the hierarchy of the given historic process instance.
     *
     * If the root process instance id of the given historic process instance is {@code null},
     * the hierarchy is ignored. This is the case for instances that were started with a version
     * prior 7.10.
     *
     * @return the builder.
     */
    public function hierarchical(): SetRemovalTimeToHistoricProcessInstancesBuilder;

    /**
     * Sets the removal time asynchronously as batch. The returned batch can be used to
     * track the progress of setting a removal time.
     *
     * @throws BadUserRequestException when no historic process instances could be found.
     * @throws AuthorizationException
     * when no {@link BatchPermissions#CREATE_BATCH_SET_REMOVAL_TIME CREATE_BATCH_SET_REMOVAL_TIME}
     * or no permission {@link Permissions#CREATE CREATE} permission is granted on {@link Resources#BATCH}.
     *
     * @return the batch which sets the removal time asynchronously.
     */
    public function executeAsync(): BatchInterface;
}
