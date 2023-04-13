<?php

namespace Jabe\Telemetry;

interface TelemetryDataInterface
{
    /**
     * This method returns a String which is unique for each installation of
     * engine. It is stored once per database so all engines connected to the
     * same database will have the same installation ID. The ID is used to
     * identify a single installation of engine.
     */
    public function getInstallation(): ?string;

    /**
     * Returns a data object that stores information about the used engine
     */
    public function getProduct(): ProductInterface;
}
