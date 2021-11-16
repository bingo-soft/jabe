<?php

namespace BpmPlatform\Engine;

use BpmPlatform\Engine\Application\ProcessApplicationInfoInterface;

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
     * @return the {@link ProcessApplicationInfo} object or null if no such process application is deployed.
     */
    public function getProcessApplicationInfo(string $processApplicationName): ProcessApplicationInfoInterface;
}
