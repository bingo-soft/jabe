<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\Authorization\{
    AuthorizationInterface,
    PermissionInterface,
    ResourceInterface,
    Permissions,
    Resources
};
use Jabe\History\{
    DurationReportResultInterface,
    HistoricTaskInstanceReportInterface
};
use Jabe\Impl\TaskReportImpl;
use Jabe\Impl\Persistence\AbstractManager;

class TaskReportManager extends AbstractManager
{
    public function __construct(...$args)
    {
        parent::__construct(...$args);
    }

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

    public function configureQuery(/*TaskReportImpl|HistoricTaskInstanceReportImpl*/$parameter, ?ResourceInterface $resource = null, ?string $queryParam = "RES.ID_", ?PermissionInterface $permission = null)
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
