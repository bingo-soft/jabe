<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Authorization\{
    AuthorizationInterface,
    Permissions,
    Resources
};
use Jabe\Engine\History\{
    DurationReportResultInterface,
    HistoricTaskInstanceReportInterface
};
use Jabe\Engine\Impl\TaskReportImpl;
use Jabe\Engine\Impl\Persistence\AbstractManager;

class TaskReportManager extends AbstractManager
{
    public function createTaskCountByCandidateGroupReport(TaskReportImpl $query): array
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectListWithRawParameter("selectTaskCountByCandidateGroupReportQuery", $query, 0, PHP_INT_MAX);
    }

    public function selectHistoricTaskInstanceCountByTaskNameReport(HistoricTaskInstanceReportImpl $query): array
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectListWithRawParameter("selectHistoricTaskInstanceCountByTaskNameReport", $query, 0, PHP_INT_MAX);
    }

    public function selectHistoricTaskInstanceCountByProcDefKeyReport(HistoricTaskInstanceReportImpl $query): array
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectListWithRawParameter("selectHistoricTaskInstanceCountByProcDefKeyReport", $query, 0, PHP_INT_MAX);
    }

    public function createHistoricTaskDurationReport(HistoricTaskInstanceReportImpl $query): array
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectListWithRawParameter("selectHistoricTaskInstanceDurationReport", $query, 0, PHP_INT_MAX);
    }

    protected function configureQuery($parameter): void
    {
        if ($parameter instanceof TaskReportImpl) {
            $this->getAuthorizationManager()->checkAuthorization(Permissions::read(), Resources::task(), AuthorizationInterface::ANY);
            $this->getTenantManager()->configureTenantCheck($parameter->getTenantCheck());
        } elseif ($parameter instanceof HistoricTaskInstanceReportImpl) {
            $this->getAuthorizationManager()->checkAuthorization(Permissions::readHistory(), Resources::processDefinition(), AuthorizationInterface::ANY);
            $this->getTenantManager()->configureTenantCheck($parameter->getTenantCheck());
        }
    }
}
