<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Authorization\{
    Permissions,
    Resources
};
use Jabe\Engine\Batch\BatchStatisticsInterface;
use Jabe\Engine\Impl\{
    ActivityStatisticsQueryImpl,
    //HistoricDecisionInstanceStatisticsQueryImp,
    DeploymentStatisticsQueryImpl,
    Page,
    ProcessDefinitionStatisticsQueryImpl
};
use Jabe\Engine\Impl\Batch\BatchStatisticsQueryImpl;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Persistence\AbstractManager;
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Management\{
    DeploymentStatisticsInterface,
    ProcessDefinitionStatisticsInterface
};

class StatisticsManager extends AbstractManager
{
    public function getStatisticsGroupedByProcessDefinitionVersion(ProcessDefinitionStatisticsQueryImpl $query, Page $page): array
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectList("selectProcessDefinitionStatistics", $query, $page);
    }

    public function getStatisticsCountGroupedByProcessDefinitionVersion(ProcessDefinitionStatisticsQueryImpl $query): int
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectOne("selectProcessDefinitionStatisticsCount", $query);
    }

    public function getStatisticsGroupedByActivity(ActivityStatisticsQueryImpl $query, Page $page): array
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectList("selectActivityStatistics", $query, $page);
    }

    public function getStatisticsCountGroupedByActivity(ActivityStatisticsQueryImpl $query): int
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectOne("selectActivityStatisticsCount", $query);
    }

    public function getStatisticsGroupedByDeployment(DeploymentStatisticsQueryImpl $query, Page $page): array
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectList("selectDeploymentStatistics", $query, $page);
    }

    public function getStatisticsCountGroupedByDeployment(DeploymentStatisticsQueryImpl $query): int
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectOne("selectDeploymentStatisticsCount", $query);
    }

    public function getStatisticsGroupedByBatch(BatchStatisticsQueryImpl $query, Page $page): array
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectList("selectBatchStatistics", $query, $page);
    }

    public function getStatisticsCountGroupedByBatch(BatchStatisticsQueryImpl $query): int
    {
        $this->configureQuery($query);
        return $this->getDbEntityManager()->selectOne("selectBatchStatisticsCount", $query);
    }

    /*protected function configureQuery(ProcessDefinitionStatisticsQueryImpl $query): void
    {
        $this->getAuthorizationManager()->configureProcessDefinitionStatisticsQuery($query);
        $this->getTenantManager()->configureQuery($query);
    }*/

    protected function configureQuery($query): void
    {
        if ($query instanceof ActivityStatisticsQueryImpl) {
            $this->checkReadProcessDefinition($query);
            $this->getAuthorizationManager()->configureActivityStatisticsQuery($query);
            $this->getTenantManager()->configureQuery($query);
        } elseif ($query instanceof  BatchStatisticsQueryImpl) {
            $this->getAuthorizationManager()->configureBatchStatisticsQuery($batchQuery);
            $this->getTenantManager()->configureQuery($batchQuery);
        } elseif ($query instanceof  DeploymentStatisticsQueryImpl) {
            $this->getAuthorizationManager()->configureDeploymentStatisticsQuery($query);
            $this->getTenantManager()->configureQuery($query);
        }
    }

    /*protected function checkReadProcessDefinition(ActivityStatisticsQueryImpl $query): void
    {
        $commandContext = $this->getCommandContext();
        if ($this->isAuthorizationEnabled() && $this->getCurrentAuthentication() != null && $commandContext->isAuthorizationCheckEnabled()) {
            $processDefinitionId = $query->getProcessDefinitionId();
            $definition = $this->getProcessDefinitionManager()->findLatestProcessDefinitionById($processDefinitionId);
            EnsureUtil::ensureNotNull("no deployed process definition found with id '" . $processDefinitionId . "'", "processDefinition", $definition);
            $this->getAuthorizationManager()->checkAuthorization(Permissions::read(), Resources::processDefinition(), $definition->getKey());
        }
    }

    public function getStatisticsCountGroupedByDecisionRequirementsDefinition(HistoricDecisionInstanceStatisticsQueryImpl decisionRequirementsDefinitionStatisticsQuery) {
        configureQuery(decisionRequirementsDefinitionStatisticsQuery);
        return (Long) getDbEntityManager()->selectOne("selectDecisionDefinitionStatisticsCount", decisionRequirementsDefinitionStatisticsQuery);
    }

    protected void configureQuery(HistoricDecisionInstanceStatisticsQueryImpl decisionRequirementsDefinitionStatisticsQuery) {
      checkReadDecisionRequirementsDefinition(decisionRequirementsDefinitionStatisticsQuery);
      getTenantManager().configureQuery(decisionRequirementsDefinitionStatisticsQuery);
    }

    protected void checkReadDecisionRequirementsDefinition(HistoricDecisionInstanceStatisticsQueryImpl $query) {
      CommandContext commandContext = getCommandContext();
      if (isAuthorizationEnabled() && getCurrentAuthentication() != null && commandContext.isAuthorizationCheckEnabled()) {
        String decisionRequirementsDefinitionId = query.getDecisionRequirementsDefinitionId();
        DecisionRequirementsDefinition definition = getDecisionRequirementsDefinitionManager().findDecisionRequirementsDefinitionById(decisionRequirementsDefinitionId);
        ensureNotNull("no deployed decision requirements definition found with id '" + decisionRequirementsDefinitionId + "'", "decisionRequirementsDefinition", definition);
        getAuthorizationManager().checkAuthorization(READ, DECISION_REQUIREMENTS_DEFINITION, definition.getKey());
      }
    }

    public List<HistoricDecisionInstanceStatistics> getStatisticsGroupedByDecisionRequirementsDefinition(HistoricDecisionInstanceStatisticsQueryImpl $query, Page page) {
      $this->configureQuery($query);
      return $this->getDbEntityManager()->selectList("selectDecisionDefinitionStatistics", $query, $page);
    }*/
}
