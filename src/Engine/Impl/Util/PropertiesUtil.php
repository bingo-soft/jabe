<?php

namespace Jabe\Engine\Impl\Util;

use Jabe\Engine\Impl\ProcessEngineLogger;

class PropertiesUtil
{
    //protected static final EngineUtilLogger LOG = ProcessEngineLogger.UTIL_LOGGER;


    public static function getProperties(string $propertiesFile)
    {
        $fd = fopen($propertiesFile, 'r');
        $properies = [];
        while (($line = fgets($fd)) !== false) {
            $pair = explode('=', $line);
            $properies[$pair[0]] = trim($pair[1]);
        }
        fclose($fd);
        return $properies;
    }
}
