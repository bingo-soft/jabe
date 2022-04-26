<?php

namespace Jabe\Engine\Application;

interface ProcessApplicationReferenceInterface
{
    /**
     * @return the name of the process application
     */
    public function getName(): string;

    /**
     * Get the process application.
     *
     * @return the {@link AbstractProcessApplication}
     * @throws ProcessApplicationUnavailableException
     *           if the process application is unavailable
     */
    public function getProcessApplication(): ?AbstractProcessApplication;
}
