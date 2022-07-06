<?php

namespace Jabe\Engine\Impl\Management;

interface PurgeReportingInterface
{
    /**
     * Adds the key value pair as report information to the current purge report.
     *
     * @param key the report key
     * @param value the report value
     */
    public function addPurgeInformation(string $key, $value): void;

    /**
     * Returns the current purge report.
     *
     * @return array the purge report
     */
    public function getPurgeReport(): array;

    /**
     * Transforms and returns the purge report to a string.
     *
     * @return string the purge report as string
     */
    public function getPurgeReportAsString(): string;

    /**
     * Returns the value for the given key.
     *
     * @param key the key which exist in the current report
     * @return mixed the corresponding value
     */
    public function getReportValue(string $key);

    /**
     * Returns true if the key is present in the current report.
     * @param key the key
     * @return bool - true if the key is present
     */
    public function containsReport(string $key): bool;

    /**
     * Returns true if the report is empty.
     *
     * @return bool - true if the report is empty, false otherwise
     */
    public function isEmpty(): bool;
}
