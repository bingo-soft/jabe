<?php

namespace Jabe\Application\Impl;

use Jabe\ProcessEngineInterface;
use Jabe\Application\{
    AbstractProcessApplication,
    ProcessApplicationReferenceInterface
};

class ProcessApplicationReferenceImpl implements ProcessApplicationReferenceInterface
{
    //private static ProcessApplicationLogger LOG = ProcessEngineLogger.PROCESS_APPLICATION_LOGGER;

    /** reference to the process application */
    protected $processApplication;

    protected $name;

    public function __construct(AbstractProcessApplication $processApplication)
    {
        $this->processApplication = $processApplication;
        $this->name = $processApplication->getName();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getProcessApplication(): ?AbstractProcessApplication
    {
        $application = $this->processApplication->get();
        if ($application === null) {
            //throw LOG.processApplicationUnavailableException(name);
            fwrite(STDERR, "Process application unavailable: $name\n");
        } else {
            return $application;
        }
    }

    public function processEngineStopping(ProcessEngineInterface $processEngine): void
    {
      // do nothing
    }

    public function clear(): void
    {
        $this->processApplication->clear();
    }
}
