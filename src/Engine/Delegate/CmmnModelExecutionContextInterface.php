<?php

namespace Jabe\Engine\Delegate;

use Jabe\Model\Cmmn\CmmnModelInstanceInterface;
use Jabe\Model\Cmmn\Instance\CmmnElementInterface;

interface CmmnModelExecutionContextInterface
{
    /**
     * Returns the {@link CmmnModelInstance} for the currently executed Cmmn Model
     *
     * @return the current {@link CmmnModelInstance}
     */
    public function getCmmnModelInstance(): CmmnModelInstanceInterface;

    /**
     * <p>Returns the currently executed Element in the Cmmn Model. This method returns a {@link CmmnElement} which may be casted
     * to the concrete type of the Cmmn Model Element currently executed.</p>
     *
     * @return the {@link CmmnElement} corresponding to the current Cmmn Model Element
     */
    public function getCmmnModelElementInstance(): CmmnElementInterface;
}
