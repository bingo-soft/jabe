<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\History\ReportResultInterface;
use BpmPlatform\Engine\Impl\HistoricProcessInstanceReportImpl;
use BpmPlatform\Engine\Impl\Persistence\AbstractManager;

class ReportManager extends AbstractManager
{
    public function selectHistoricProcessInstanceDurationReport(HistoricProcessInstanceReportImpl $query): array
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectListWithRawParameter("selectHistoricProcessInstanceDurationReport", $query, 0, INF);
    }

    protected function configureQuery(HistoricProcessInstanceReportImpl $parameter): void
    {
        $this->getTenantManager()->configureTenantCheck($parameter->getTenantCheck());
    }
}
