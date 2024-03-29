<?php

namespace Jabe\Application;

interface ProcessApplicationReferenceInterface
{
    /**
     * @return string the name of the process application
     */
    public function getName(): ?string;

    /**
     * Get the process application.
     *
     * @return AbstractProcessApplication the AbstractProcessApplication
     * @throws ProcessApplicationUnavailableException
     *           if the process application is unavailable
     */
    public function getProcessApplication(): ?AbstractProcessApplication;
}
