<?php

namespace Jabe\Engine\Impl\Cfg;

use Jabe\Engine\ProcessEngineConfiguration;
use Jabe\Engine\Variable\SerializationDataFormats;

abstract class ProcessEngineConfigurationImpl extends ProcessEngineConfiguration
{
    protected $defaultSerializationFormat = SerializationDataFormats::PHP;

    public function getDefaultSerializationFormat(): string
    {
        return $this->defaultSerializationFormat;
    }
}
