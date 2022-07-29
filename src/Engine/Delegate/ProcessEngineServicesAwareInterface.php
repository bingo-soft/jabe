<?php

namespace Jabe\Engine\Delegate;

use Jabe\Engine\{
    ProcessEngineInterface,
    ProcessEngineServicesInterface
};

interface ProcessEngineServicesAwareInterface
{
    /**
     * Returns the ProcessEngineServices providing access to the
     * public API of the process engine.
     *
     * @return ProcessEngineServicesInterface the ProcessEngineServices.
     */
    public function getProcessEngineServices(): ProcessEngineServicesInterface;

    /**
     * Returns the ProcessEngine providing access to the
     * public API of the process engine.
     *
     * @return ProcessEngineInterface the ProcessEngine.
     */
    public function getProcessEngine(): ProcessEngineInterface;
}
