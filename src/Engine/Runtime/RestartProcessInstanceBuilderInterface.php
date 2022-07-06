<?php

namespace Jabe\Engine\Runtime;

use Jabe\Engine\Batch\BatchInterface;
use Jabe\Engine\History\HistoricProcessInstanceQueryInterface;

interface RestartProcessInstanceBuilderInterface extends InstantiationBuilderInterface
{
    /**
     * @param query a query which selects the historic process instances to restart.
     * Query results are restricted to process instances for which the user has {@link Permissions#READ_HISTORY} permission.
     */
    public function historicProcessInstanceQuery(HistoricProcessInstanceQueryInterface $query): RestartProcessInstanceBuilderInterface;

    /**
     * @param processInstanceIds the process instance ids to restart.
     */
    public function processInstanceIds(array $processInstanceIds): RestartProcessInstanceBuilderInterface;

    /**
     * Sets the initial set of variables during restart. By default, the last set of variables is used
     */
    public function initialSetOfVariables(): RestartProcessInstanceBuilderInterface;

    /**
     * Does not take over the business key of the historic process instance
     */
    public function withoutBusinessKey(): RestartProcessInstanceBuilderInterface;

    /**
     * Skips custom execution listeners when creating activity instances during restart
     */
    public function skipCustomListeners(): RestartProcessInstanceBuilderInterface;

    /**
     * Skips io mappings when creating activity instances during restart
     */
    public function skipIoMappings(): RestartProcessInstanceBuilderInterface;

    /**
     * Executes the restart synchronously.
     * @throws BadUserRequestException
     *   When the affected instances count exceeds the maximum results limit. A maximum results
     *   limit can be specified with the process engine configuration property
     *   <code>queryMaxResultsLimit</code> (default {@link Integer#MAX_VALUE}).
     *   Please use the batch operation {@link #executeAsync()} instead.
     */
    public function execute(): void;

    /**
     * Executes the restart asynchronously as batch. The returned batch
     * can be used to track the progress of the restart.
     *
     * @return BatchInterface the batch which executes the restart asynchronously.
     *
     * @throws AuthorizationException
     *   if the user has not all of the following permissions
     *   <ul>
     *     <li>{@link Permissions#CREATE} or {@link BatchPermissions#CREATE_BATCH_RESTART_PROCESS_INSTANCES} permission on {@link Resources#BATCH}</li>
     *   </ul>
     */
    public function executeAsync(): BatchInterface;
}
