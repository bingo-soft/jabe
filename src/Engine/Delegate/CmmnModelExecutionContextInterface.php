<?php

namespace Jabe\Engine\Delegate;

use Jabe\Model\Cmmn\CmmnModelInstanceInterface;
use Jabe\Model\Cmmn\Instance\CmmnElementInterface;

interface CmmnModelExecutionContextInterface
{
    /**
     * Returns the CmmnModelInstance for the currently executed Cmmn Model
     *
     * @return CmmnModelInstanceInterface the current CmmnModelInstance
     */
    public function getCmmnModelInstance(): CmmnModelInstanceInterface;

    /**
     * <p>Returns the currently executed Element in the Cmmn Model. This method returns a CmmnElement which may be casted
     * to the concrete type of the Cmmn Model Element currently executed.</p>
     *
     * @return CmmnElementInterface the CmmnElement corresponding to the current Cmmn Model Element
     */
    public function getCmmnModelElementInstance(): CmmnElementInterface;
}
