<?php

namespace BpmPlatform\Engine\Impl\Pvm\Process;

interface AsyncBeforeUpdateInterface
{
    /**
     * Method which is called if the asyncBefore property should be updated.
     *
     * @param asyncBefore the new value for the asyncBefore flag
     * @param exclusive the exclusive flag
     */
    public function updateAsyncBefore(bool $asyncBefore, bool $exclusive): void;
}
