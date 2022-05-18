<?php

namespace Jabe\Engine\Telemetry;

interface TelemetryDataInterface
{
    /**
     * This method returns a String which is unique for each installation of
     * Camunda. It is stored once per database so all engines connected to the
     * same database will have the same installation ID. The ID is used to
     * identify a single installation of Camunda Platform.
     */
    public function getInstallation(): string;

    /**
     * Returns a data object that stores information about the used Camunda
     * product.
     */
    public function getProduct(): ProductInterface;
}
