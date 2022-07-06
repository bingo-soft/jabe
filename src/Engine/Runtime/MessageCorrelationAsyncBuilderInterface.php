<?php

namespace Jabe\Engine\Runtime;

use Jabe\Engine\Batch\BatchInterface;
use Jabe\Engine\History\HistoricProcessInstanceQueryInterface;

interface MessageCorrelationAsyncBuilderInterface
{

    /**
     * <p>
     * Correlate the message such that the process instances with the given ids
     * are selected.
     * </p>
     *
     * @param ids
     *          the ids of the process instances to correlate to; at least one of
     *          {@link #processInstanceIds(List)},
     *          {@link #processInstanceQuery(ProcessInstanceQuery)}, or
     *          {@link #historicProcessInstanceQuery(HistoricProcessInstanceQuery)}
     *          has to be set.
     * @return MessageCorrelationAsyncBuilderInterface the builder
     * @throws NullValueException
     *           when <code>ids</code> is <code>null</code>
     */
    public function processInstanceIds(array $ids): MessageCorrelationAsyncBuilderInterface;

    /**
     * <p>
     * Correlate the message such that the process instances found by the given
     * query are selected.
     * </p>
     *
     * @param processInstanceQuery
     *          the query to select process instances to correlate to; at least
     *          one of {@link #processInstanceIds(List)},
     *          {@link #processInstanceQuery(ProcessInstanceQuery)}, or
     *          {@link #historicProcessInstanceQuery(HistoricProcessInstanceQuery)}
     *          has to be set.
     * @return MessageCorrelationAsyncBuilderInterface the builder
     * @throws NullValueException
     *           when <code>processInstanceQuery</code> is <code>null</code>
     */
    public function processInstanceQuery(ProcessInstanceQueryInterface $processInstanceQuery): MessageCorrelationAsyncBuilderInterface;

    /**
     * <p>
     * Correlate the message such that the process instances found by the given
     * query are selected.
     * </p>
     *
     * @param historicProcessInstanceQuery
     *          the query to select process instances to correlate to; at least
     *          one of {@link #processInstanceIds(List)},
     *          {@link #processInstanceQuery(ProcessInstanceQuery)}, or
     *          {@link #historicProcessInstanceQuery(HistoricProcessInstanceQuery)}
     *          has to be set.
     * @return MessageCorrelationAsyncBuilderInterface the builder
     * @throws NullValueException
     *           when <code>historicProcessInstanceQuery</code> is <code>null</code>
     */
    public function historicProcessInstanceQuery(HistoricProcessInstanceQueryInterface $historicProcessInstanceQuery): MessageCorrelationAsyncBuilderInterface;

    /**
     * <p>
     * Pass a variable to the execution waiting on the message. Use this method
     * for passing the message's payload.
     * </p>
     *
     * <p>
     * Invoking this method multiple times allows passing multiple variables.
     * </p>
     *
     * @param variableName
     *          the name of the variable to set
     * @param variableValue
     *          the value of the variable to set
     * @return MessageCorrelationAsyncBuilderInterface the builder
     * @throws NullValueException
     *           when <code>variableName</code> is <code>null</code>
     */
    public function setVariable(string $variableName, $variableValue): MessageCorrelationAsyncBuilderInterface;

    /**
     * <p>
     * Pass a map of variables to the execution waiting on the message. Use this
     * method for passing the message's payload
     * </p>
     *
     * @param variables
     *          the map of variables
     * @return MessageCorrelationAsyncBuilderInterface the builder
     */
    public function setVariables(array $variables): MessageCorrelationAsyncBuilderInterface;

    /**
     * Correlates a message asynchronously to executions that are waiting for this
     * message based on the provided queries and list of process instance ids,
     * whereby query results and list of ids will be merged.
     *
     * @return BatchInterface the batch which correlates the message asynchronously
     *
     * @throws NullValueException
     *           when neither {@link #processInstanceIds(List)},
     *           {@link #processInstanceQuery(ProcessInstanceQuery)}, nor
     *           {@link #historicProcessInstanceQuery(HistoricProcessInstanceQuery)}}
     *           have been set
     * @throws BadUserRequestException
     *           when no process instances are found with the given ids or queries
     * @throws AuthorizationException
     *           when the user has no {@link BatchPermissions#CREATE} or
     *           {@link BatchPermissions#CREATE_BATCH_SET_VARIABLES} permission on
     *           {@link Resources#BATCH}
     */
    public function correlateAllAsync(): BatchInterface;
}
