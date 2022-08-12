<?php

namespace Jabe\Impl\Persistence\Deploy\Cache;

class CachePurgeReport implements PurgeReporting
{
    public const PROCESS_DEF_CACHE = "PROC_DEF_CACHE";
    public const BPMN_MODEL_INST_CACHE = "BPMN_MODEL_INST_CACHE";
    public const CASE_DEF_CACHE = "CASE_DEF_CACHE";
    public const CASE_MODEL_INST_CACHE = "CASE_MODEL_INST_CACHE";
    public const DMN_DEF_CACHE = "DMN_DEF_CACHE";
    public const DMN_REQ_DEF_CACHE = "DMN_REQ_DEF_CACHE";
    public const DMN_MODEL_INST_CACHE = "DMN_MODEL_INST_CACHE";

    /**
     * Key: cache name
     * Value: values
     */
    private $deletedCache = [];

    public function addPurgeInformation(string $key, $value): void
    {
        $this->deletedCache[$key] = $value;
    }

    public function getPurgeReport(): array
    {
        return $this->deletedCache;
    }

    public function getPurgeReportAsString(): string
    {
        $builder = "";
        foreach ($this->deletedCache as $key => $value) {
            $builder .= "Cache: " . $key
                . " contains: " . implode(" ", $this->getReportValue($key))
                . "\n";
        }
        return $builder;
    }

    public function getReportValue(string $key)
    {
        if (array_key_exists($key, $this->deletedCache)) {
            return $this->deletedCache[$key];
        }
        return [];
    }

    public function containsReport(string $key): bool
    {
        return array_key_exists($key, $this->deletedCache);
    }

    public function isEmpty(): bool
    {
        return empty($this->deletedCache);
    }
}
