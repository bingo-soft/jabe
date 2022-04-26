<?php

namespace Jabe\Engine;

interface ProcessEngineInterface extends ProcessEngineServicesInterface
{

    /** the version of the process engine library */
    public const VERSION = "fox";

    /** The name as specified in 'process-engine-name' in
     * configuration file.
     * The default name for a process engine is 'default */
    public function getName(): string;

    public function close(): void;

    public function getProcessEngineConfiguration(): AbstractProcessEngineConfiguration;
}
