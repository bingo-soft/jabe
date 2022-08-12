<?php

namespace Jabe\Telemetry;

interface DatabaseInterface
{
    /**
     * The vendor of the connected database system.
     */
    public function getVendor(): string;

    /**
     * The version of the connected database system.
     */
    public function getVersion(): string;
}
