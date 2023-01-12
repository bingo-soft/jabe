<?php

namespace Jabe;

use Jabe\Application\ProcessApplicationInfoInterface;

interface ProcessApplicationServiceInterface
{
    /**
     * @returns the names of all deployed process applications
     * */
    public function getProcessApplicationNames(): array;

    /**
     * <p>Provides information about a deployed process application</p>
     *
     * @param processApplicationName
     *
     * @return ProcessApplicationInfoInterface the object or null if no such process application is deployed.
     */
    public function getProcessApplicationInfo(?string $processApplicationName): ProcessApplicationInfoInterface;
}
