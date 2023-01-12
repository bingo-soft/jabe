<?php

namespace Jabe\Application\Impl;

use Jabe\Application\{
    AbstractProcessApplication,
    ProcessApplicationExecutionException,
    ProcessApplicationReferenceInterface
};

class EmbeddedProcessApplication extends AbstractProcessApplication
{
    public const DEFAULT_NAME = "Process Application";
    //private static $LOG = ProcessEngineLogger.PROCESS_APPLICATION_LOGGER;

    protected function autodetectProcessApplicationName(): ?string
    {
        return self::DEFAULT_NAME;
    }

    public function getReference(): ?ProcessApplicationReferenceInterface
    {
        return new EmbeddedProcessApplicationReferenceImpl($this);
    }

    /**
     * Since the process engine is loaded by the same classloader
     * as the process application, nothing needs to be done.
     */
    public function execute(callable $callable)
    {
        try {
            return $callable();
        } catch (\Exception $e) {
            //throw LOG.processApplicationExecutionException(e);
        }
    }
}
