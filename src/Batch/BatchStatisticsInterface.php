<?php

namespace Jabe\Batch;

interface BatchStatisticsInterface extends BatchInterface
{
    /**
     * <p>
     *   The number of remaining batch execution jobs.
     *   This does include failed batch execution jobs and
     *   batch execution jobs which still have to be created by the seed job.
     * </p>
     *
     * <p>
     *   See
     *   {@link #getTotalJobs()} for the number of all batch execution jobs,
     *   {@link #getCompletedJobs()} for the number of completed batch execution jobs and
     *   {@link #getFailedJobs()} for the number of failed batch execution jobs.
     * </p>
     *
     * @return int the number of remaining batch execution jobs
     */
    public function getRemainingJobs(): int;

    /**
     * <p>
     *   The number of completed batch execution jobs.
     *   This does include aborted/deleted batch execution jobs.
     * </p>
     *
     * <p>
     *   See
     *   {@link #getTotalJobs()} for the number of all batch execution jobs,
     *   {@link #getRemainingJobs()} ()} for the number of remaining batch execution jobs and
     *   {@link #getFailedJobs()} for the number of failed batch execution jobs.
     * </p>
     *
     * @return int the number of completed batch execution jobs
     */
    public function getCompletedJobs(): int;

    /**
     * <p>
     *   The number of failed batch execution jobs.
     *   This does not include aborted or deleted batch execution jobs.
     * </p>
     *
     * <p>
     *   See
     *   {@link #getTotalJobs()} for the number of all batch execution jobs,
     *   {@link #getRemainingJobs()} ()} for the number of remaining batch execution jobs and
     *   {@link #getCompletedJobs()} ()} for the number of completed batch execution jobs.
     * </p>
     *
     * @return int the number of failed batch execution jobs
     */
    public function getFailedJobs(): int;
}
