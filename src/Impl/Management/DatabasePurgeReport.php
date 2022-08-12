<?php

namespace Jabe\Impl\Management;

class DatabasePurgeReport implements PurgeReportingInterface
{
    /**
     * Key: table name
     * Value: entity count
     */
    private $deletedEntities = [];
    private $dbContainsLicenseKey;

    public function addPurgeInformation(string $key, $value): void
    {
        $this->deletedEntities[$key] = $value;
    }

    public function getPurgeReport(): array
    {
        return $this->deletedEntities;
    }

    public function getPurgeReportAsString(): string
    {
        $builder = "";
        foreach ($this->deletedEntities as $key => $value) {
            $builder .= "Table: " . $key
            . " contains: " . $this->getReportValue($key)
            . " rows\n";
        }
        return $builder;
    }

    public function getReportValue(string $key)
    {
        if (array_key_exists($key, $this->deletedEntities)) {
            return $this->deletedEntities[$key];
        }
        return null;
    }

    public function containsReport(string $key): bool
    {
        return array_key_exists($key, $this->deletedEntities);
    }

    public function isEmpty(): bool
    {
        return empty($this->deletedEntities);
    }

    public function isDbContainsLicenseKey(): ?bool
    {
        return $this->dbContainsLicenseKey;
    }

    public function setDbContainsLicenseKey(bool $dbContainsLicenseKey): void
    {
        $this->dbContainsLicenseKey = $dbContainsLicenseKey;
    }
}
