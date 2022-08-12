<?php

namespace Jabe\Telemetry;

interface InternalsInterface
{
    /**
     * Information about the connected database system.
     */
    public function getDatabase(): DatabaseInterface;

    /**
     * Information about the application server Camunda is running on.
     */
    public function getApplicationServer(): ApplicationServerInterface;

    /**
     * Information about the Camunda license key issued for enterprise editions of
     * Camunda Platform. May be null when no license is used.
     */
    public function getLicenseKey(): LicenseKeyDataInterface;

    /**
     * Information about the number of command executions performed by the Camunda
     * engine. If telemetry sending is enabled, the number of executions per
     * command resets on sending the data to Camunda. Retrieving the data through
     * ManagementService#getTelemetryData() will not reset the count.
     */
    public function getCommands(): array;

    /**
     * A selection of metrics collected by the engine. Metrics included are:
     * <ul>
     *   <li>The number of root process instance executions started.</li>
     *   <li>The number of activity instances started or also known as flow node
     * instances.</li>
     *   <li>The number of executed decision instances.</li>
     *   <li>The number of executed decision elements.</li>
     * </ul>
     * The metrics reset on sending the data to Camunda. Retrieving the data
     * through ManagementService#getTelemetryData() will not reset the
     * count.
     */
    public function getMetrics(): array;

    /**
     * Used integrations
     */
    public function getIntegration();

    /**
     * Webapps enabled
     */
    public function getWebapps(): array;
}
