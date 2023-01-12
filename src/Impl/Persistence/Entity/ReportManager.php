<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Authorization\{
    PermissionInterface,
    ResourceInterface
};
use Jabe\Impl\HistoricProcessInstanceReportImpl;
use Jabe\Impl\Persistence\AbstractManager;

class ReportManager extends AbstractManager
{
    public function selectHistoricProcessInstanceDurationReport(HistoricProcessInstanceReportImpl $query): array
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectListWithRawParameter("selectHistoricProcessInstanceDurationReport", $query, 0, PHP_INT_MAX);
    }

    public function configureQuery(/*HistoricProcessInstanceReportImpl*/$parameter, ?ResourceInterface $resource = null, ?string $queryParam = "RES.ID_", ?PermissionInterface $permission = null)
    {
        $this->getTenantManager()->configureTenantCheck($parameter->getTenantCheck());
    }
}
