<?php

namespace Jabe\Engine\History;

use Jabe\Engine\Query\ReportInterface;

interface HistoricTaskInstanceReportInterface extends ReportInterface
{
    /**
     * <p>Sets the completed after date for constraining the query to search for all tasks
     * which are completed after a certain date.</p>
     *
     * @param completedAfter A {@link Date} to define the granularity of the report
     *
     * @throws NotValidException
     *          When the given date is null.
     */
    public function completedAfter(string $completedAfter): HistoricTaskInstanceReportInterface;

    /**
     * <p>Sets the completed before date for constraining the query to search for all tasks
     * which are completed before a certain date.</p>
     *
     * @param completedBefore A {@link Date} to define the granularity of the report
     *
     * @throws NotValidException
     *          When the given date is null.
     */
    public function completedBefore(string $completedBefore): HistoricTaskInstanceReportInterface;

    /**
     * <p>Executes the task report query and returns a list of {@link HistoricTaskInstanceReportResult}s</p>
     *
     * @throws AuthorizationException
     *          If the user has no {@link Permissions#READ_HISTORY} permission
     *          on any {@link Resources#PROCESS_DEFINITION}.
     *
     * @return a list of {@link HistoricTaskInstanceReportResult}s
     */
    public function countByProcessDefinitionKey(): array;

    /**
     * <p>Executes the task report query and returns a list of {@link HistoricTaskInstanceReportResult}s</p>
     *
     * @throws AuthorizationException
     *          If the user has no {@link Permissions#READ_HISTORY} permission
     *          on any {@link Resources#PROCESS_DEFINITION}.
     *
     * @return a list of {@link HistoricTaskInstanceReportResult}s
     */
    public function countByTaskName(): array;
}
