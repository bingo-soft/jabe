<?php

namespace Jabe\History;

interface DurationReportResultInterface
{
    /**
     * <p>Returns the smallest duration of all completed instances,
     * which have been started in the given period.</p>
     */
    public function getMinimum(): int;

    /**
     * <p>Returns the greatest duration of all completed instances,
     * which have been started in the given period.</p>
     */
    public function getMaximum(): int;

    /**
     * <p>Returns the average duration of all completed instances,
     * which have been started in the given period.</p>
     */
    public function getAverage(): int;
}
