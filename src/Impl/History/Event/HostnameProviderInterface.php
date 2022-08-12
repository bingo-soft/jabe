<?php

namespace Jabe\Impl\History\Event;

use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;

interface HostnameProviderInterface
{
    /**
     * Provides a <code>String</code> that identifies the host of the given Process Engine.
     *
     * @param processEngineConfiguration of the Process Engine that will run on the current host
     * @return a String identifying the current host
     */
    public function getHostname(ProcessEngineConfigurationImpl $processEngineConfiguration): string;
}
