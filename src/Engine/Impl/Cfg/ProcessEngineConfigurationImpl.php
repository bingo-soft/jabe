<?php

namespace BpmPlatform\Engine\Impl\Cfg;

use BpmPlatform\Engine\ProcessEngineConfiguration;
use BpmPlatform\Engine\Variable\SerializationDataFormats;

abstract class ProcessEngineConfigurationImpl extends ProcessEngineConfiguration
{
    protected $defaultSerializationFormat = SerializationDataFormats::PHP;

    public function getDefaultSerializationFormat(): string
    {
        return $this->defaultSerializationFormat;
    }
}
