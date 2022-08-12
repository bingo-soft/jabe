<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Impl\HistoricProcessInstanceReportImpl;
use Jabe\Impl\Persistence\AbstractManager;

class ReportManager extends AbstractManager
{
    public function selectHistoricProcessInstanceDurationReport(HistoricProcessInstanceReportImpl $query): array
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectListWithRawParameter("selectHistoricProcessInstanceDurationReport", $query, 0, PHP_INT_MAX);
    }

    protected function configureQuery(HistoricProcessInstanceReportImpl $parameter): void
    {
        $this->getTenantManager()->configureTenantCheck($parameter->getTenantCheck());
    }
}
