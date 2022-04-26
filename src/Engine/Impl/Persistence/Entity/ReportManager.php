<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\History\ReportResultInterface;
use Jabe\Engine\Impl\HistoricProcessInstanceReportImpl;
use Jabe\Engine\Impl\Persistence\AbstractManager;

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
