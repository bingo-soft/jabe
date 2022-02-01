<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\Authorization\{
    AuthorizationInterface,
    Permissions,
    Resources
};
use BpmPlatform\Engine\History\{
    DurationReportResultInterface,
    HistoricTaskInstanceReportInterface
};
use BpmPlatform\Engine\Impl\TaskReportImpl;
use BpmPlatform\Engine\Impl\Persistence\AbstractManager;
use BpmPlatform\Engine\Task\TaskCountByCandidateGroupResultInterface;

class TaskReportManager extends AbstractManager
{
    public function createTaskCountByCandidateGroupReport(TaskReportImpl $query): array
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectListWithRawParameter("selectTaskCountByCandidateGroupReportQuery", $query, 0, INF);
    }

    public function selectHistoricTaskInstanceCountByTaskNameReport(HistoricTaskInstanceReportImpl $query): array
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectListWithRawParameter("selectHistoricTaskInstanceCountByTaskNameReport", $query, 0, INF);
    }

    public function selectHistoricTaskInstanceCountByProcDefKeyReport(HistoricTaskInstanceReportImpl $query): array
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectListWithRawParameter("selectHistoricTaskInstanceCountByProcDefKeyReport", $query, 0, INF);
    }

    public function createHistoricTaskDurationReport(HistoricTaskInstanceReportImpl $query): array
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectListWithRawParameter("selectHistoricTaskInstanceDurationReport", $query, 0, INF);
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
