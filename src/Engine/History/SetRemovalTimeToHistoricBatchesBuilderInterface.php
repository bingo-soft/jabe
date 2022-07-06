<?php

namespace Jabe\Engine\History;

use Jabe\Engine\Batch\BatchInterface;
use Jabe\Engine\Batch\History\HistoricBatchQueryInterface;

interface SetRemovalTimeToHistoricBatchesBuilderInterface
{
    /**
    * Selects historic batches by the given query.
    *
    * @param historicBatchQuery to be evaluated.
    * @return SetRemovalTimeToHistoricBatchesBuilderInterface the builder.
    */
    public function byQuery(HistoricBatchQueryInterface $historicBatchQuery): SetRemovalTimeToHistoricBatchesBuilderInterface;

   /**
    * Selects historic batches by the given ids.
    *
    * @param historicBatchIds supposed to be affected.
    * @return SetRemovalTimeToHistoricBatchesBuilderInterface the builder.
    */
    public function byIds(array $historicBatchIds): SetRemovalTimeToHistoricBatchesBuilderInterface;

    /**
    * Sets the removal time asynchronously as batch. The returned batch can be used to
    * track the progress of setting a removal time.
    *
    * @throws BadUserRequestException when no historic batches could be found.
    * @throws AuthorizationException
    * when no {@link BatchPermissions#CREATE_BATCH_SET_REMOVAL_TIME CREATE_BATCH_SET_REMOVAL_TIME}
    * or no permission {@link Permissions#CREATE CREATE} permission is granted on {@link Resources#BATCH}.
    *
    * @return BatchInterface the batch which sets the removal time asynchronously.
    */
    public function executeAsync(): BatchInterface;
}
