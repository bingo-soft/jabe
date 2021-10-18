<?php

namespace BpmPlatform\Engine\Delegate;

use BpmPlatform\Engine\{
    ProcessEngineInterface,
    ProcessEngineServicesInterface
};

interface ProcessEngineServicesAwareInterface
{
    /**
     * Returns the {@link ProcessEngineServices} providing access to the
     * public API of the process engine.
     *
     * @return the {@link ProcessEngineServices}.
     */
    public function getProcessEngineServices(): ProcessEngineServicesInterface;

    /**
     * Returns the {@link ProcessEngine} providing access to the
     * public API of the process engine.
     *
     * @return the {@link ProcessEngine}.
     */
    public function getProcessEngine(): ProcessEngineInterface;
}
