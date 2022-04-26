<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Authorization\{
    Permissions,
    Resources
};
use Jabe\Engine\History\HistoricActivityStatisticsInterface;
use Jabe\Engine\Impl\{
    HistoricActivityStatisticsQueryImpl,
    Page
};
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Persistence\AbstractManager;

class HistoricStatisticsManager extends AbstractManager
{
    public function getHistoricStatisticsGroupedByActivity(HistoricActivityStatisticsQueryImpl $query, Page $page): array
    {
        if ($this->ensureHistoryReadOnProcessDefinition($query)) {
            return $this->getDbEntityManager()->selectList("selectHistoricActivityStatistics", $query, $page);
        } else {
            return [];
        }
    }

    public function getHistoricStatisticsCountGroupedByActivity(HistoricActivityStatisticsQueryImpl $query): int
    {
        if ($this->ensureHistoryReadOnProcessDefinition($query)) {
            return $this->getDbEntityManager()->selectOne("selectHistoricActivityStatisticsCount", $query);
        } else {
            return 0;
        }
    }

    /*public function getHistoricStatisticsGroupedByCaseActivity(HistoricCaseActivityStatisticsQueryImpl query, Page page) {
        return getDbEntityManager().selectList("selectHistoricCaseActivityStatistics", query, page);
    }

    public long getHistoricStatisticsCountGroupedByCaseActivity(HistoricCaseActivityStatisticsQueryImpl query) {
        return (Long) getDbEntityManager().selectOne("selectHistoricCaseActivityStatisticsCount", query);
    }*/

    protected function ensureHistoryReadOnProcessDefinition(HistoricActivityStatisticsQueryImpl $query): bool
    {
        $commandContext = $this->getCommandContext();

        if ($this->isAuthorizationEnabled() && $this->getCurrentAuthentication() != null && $commandContext->isAuthorizationCheckEnabled()) {
            $processDefinitionId = $query->getProcessDefinitionId();
            $definition = $this->getProcessDefinitionManager()->findLatestProcessDefinitionById($processDefinitionId);

            if ($definition == null) {
                return false;
            }

            return $this->getAuthorizationManager()->isAuthorized(Permissions::readHistory(), Resources::processDefinition(), $definition->getKey());
        }

        return true;
    }
}
