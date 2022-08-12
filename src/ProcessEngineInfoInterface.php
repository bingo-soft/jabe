<?php

namespace Jabe;

interface ProcessEngineInfoInterface
{
    /**
     * Returns the name of the process engine.
     */
    public function getName(): string;

    /**
     * Returns the resources the engine was configured from.
     */
    public function getResourceUrl(): string;

    /**
     * Returns the exception stacktrace in case an exception occurred while initializing
     * the engine. When no exception occured, null is returned.
     */
    public function getException(): string;
}
