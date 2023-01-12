<?php

namespace Jabe\Telemetry;

interface ApplicationServerInterface
{
    /**
     * The vendor of the installed application server.
     */
    public function getVendor(): ?string;

    /**
     * The version of the installed application server.
     */
    public function getVersion(): ?string;
}
