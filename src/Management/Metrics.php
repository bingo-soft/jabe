<?php

namespace Jabe\Management;

class Metrics
{
    public const ACTIVTY_INSTANCE_START = "activity-instance-start";
    public const ACTIVTY_INSTANCE_END = "activity-instance-end";

    /**
     * Number of times job acqusition is performed
     */
    public const JOB_ACQUISITION_ATTEMPT = "job-acquisition-attempt";

    /**
     * Number of jobs successfully acquired (i.e. selected + locked)
     */
    public const JOB_ACQUIRED_SUCCESS = "job-acquired-success";
    /**
     * Number of jobs attempted to acquire but with failure (i.e. selected + lock failed)
     */
    public const JOB_ACQUIRED_FAILURE = "job-acquired-failure";

    /**
     * Number of jobs that were submitted for execution but were rejected due to
     * resource shortage. In the default job executor, this is the case when
     * the execution queue is full.
     */
    public const JOB_EXECUTION_REJECTED = "job-execution-rejected";

    public const JOB_SUCCESSFUL = "job-successful";
    public const JOB_FAILED = "job-failed";

    /**
     * Number of jobs that are immediately locked and executed because they are exclusive
     * and created in the context of job execution
     */
    public const JOB_LOCKED_EXCLUSIVE = "job-locked-exclusive";

    /**
     * Number of executed Root Process Instance executions.
     */
    public const ROOT_PROCESS_INSTANCE_START = "root-process-instance-start";

    /**
     * Number of executed decision elements in the DMN engine.
     */
    public const EXECUTED_DECISION_INSTANCES = "executed-decision-instances";
    public const EXECUTED_DECISION_ELEMENTS = "executed-decision-elements";

    /**
     * Number of instances removed by history cleanup.
     */
    public const HISTORY_CLEANUP_REMOVED_PROCESS_INSTANCES = "history-cleanup-removed-process-instances";
    public const HISTORY_CLEANUP_REMOVED_CASE_INSTANCES = "history-cleanup-removed-case-instances";
    public const HISTORY_CLEANUP_REMOVED_DECISION_INSTANCES = "history-cleanup-removed-decision-instances";
    public const HISTORY_CLEANUP_REMOVED_BATCH_OPERATIONS = "history-cleanup-removed-batch-operations";
    public const HISTORY_CLEANUP_REMOVED_TASK_METRICS = "history-cleanup-removed-task-metrics";

    /**
     * Number of unique task workers
     */
    public const UNIQUE_TASK_WORKERS = "unique-task-workers";
}
