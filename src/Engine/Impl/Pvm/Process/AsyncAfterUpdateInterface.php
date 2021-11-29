<?php

namespace BpmPlatform\Engine\Impl\Pvm\Process;

interface AsyncAfterUpdateInterface
{
    /**
     * Method which is called if the asyncAfter property should be updated.
     *
     * @param asyncAfter the new value for the asyncBefore flag
     * @param exclusive the exclusive flag
     */
    public function updateAsyncAfter(bool $asyncAfter, bool $exclusive): void;
}
