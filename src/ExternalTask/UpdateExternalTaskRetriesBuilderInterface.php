<?php

namespace Jabe\ExternalTask;

use Jabe\Batch\BatchInterface;

interface UpdateExternalTaskRetriesBuilderInterface extends UpdateExternalTaskRetriesSelectBuilderInterface
{
    /**
     * Sets the retries for external tasks.
     */
    public function set(int $retries): void;

    /**
     * Sets the retries for external tasks asynchronously as batch. The returned batch
     * can be used to track the progress.
     */
    public function setAsync(int $retries): BatchInterface;
}
