<?php

namespace Jabe\Impl\Management;

use Jabe\Impl\Persistence\Deploy\Cache\CachePurgeReport;

class PurgeReport
{
    private $databasePurgeReport;
    private $cachePurgeReport;

    public function getDatabasePurgeReport(): DatabasePurgeReport
    {
        return $this->databasePurgeReport;
    }

    public function setDatabasePurgeReport(DatabasePurgeReport $databasePurgeReport): void
    {
        $this->databasePurgeReport = $databasePurgeReport;
    }

    public function getCachePurgeReport(): CachePurgeReport
    {
        return $this->cachePurgeReport;
    }

    public function setCachePurgeReport(CachePurgeReport $cachePurgeReport): void
    {
        $this->cachePurgeReport = $cachePurgeReport;
    }

    public function isEmpty(): bool
    {
        return $this->cachePurgeReport->isEmpty() && $this->databasePurgeReport->isEmpty();
    }
}
