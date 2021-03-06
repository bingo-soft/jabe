<?php

namespace Jabe\Engine\Impl\History\Event;

//use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;

class SimpleIpBasedProvider implements HostnameProviderInterface
{
    //private final static ProcessEngineLogger LOG = ProcessEngineLogger.INSTANCE;

    public function getHostname(ProcessEngineConfigurationImpl $processEngineConfiguration): string
    {
        $localIp = gethostname();
        return self::createId($localIp, $processEngineConfiguration->getProcessEngineName());
    }

    public static function createId(string $ip, string $engineName): string
    {
        return $ip . "$" . $engineName;
    }
}
