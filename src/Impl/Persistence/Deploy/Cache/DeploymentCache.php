<?php

namespace Jabe\Impl\Persistence\Deploy\Cache;

use Jabe\ProcessEngineException;
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Persistence\Entity\{
    FormDefinitionEntity,
    DeploymentEntity,
    ProcessDefinitionEntity
};
use Bpmn\BpmnModelInstanceInterface;
use Jabe\Commons\Utils\Cache\CacheInterface;

class DeploymentCache
{
    protected $processDefinitionEntityCache;
    //protected CaseDefinitionCache caseDefinitionCache;
    //protected DecisionDefinitionCache decisionDefinitionCache;
    //protected DecisionRequirementsDefinitionCache decisionRequirementsDefinitionCache;
    protected $formDefinitionCache;

    protected $bpmnModelInstanceCache;
    //protected CmmnModelInstanceCache cmmnModelInstanceCache;
    //protected DmnModelInstanceCache dmnModelInstanceCache;
    protected $cacheDeployer;

    public function __construct(CacheFactoryInterface $factory, int $cacheCapacity)
    {
        $this->cacheDeployer = new CacheDeployer();
        $this->processDefinitionEntityCache = new ProcessDefinitionCache($factory, $cacheCapacity, $this->cacheDeployer);
        //caseDefinitionCache = new CaseDefinitionCache(factory, cacheCapacity, cacheDeployer);
        //decisionDefinitionCache = new DecisionDefinitionCache(factory, cacheCapacity, cacheDeployer);
        //decisionRequirementsDefinitionCache = new DecisionRequirementsDefinitionCache(factory, cacheCapacity, cacheDeployer);
        $this->formDefinitionCache = new FormDefinitionCache($factory, $cacheCapacity, $this->cacheDeployer);

        $this->bpmnModelInstanceCache = new BpmnModelInstanceCache($factory, $cacheCapacity, $this->processDefinitionEntityCache);
        //cmmnModelInstanceCache = new CmmnModelInstanceCache(factory, cacheCapacity, caseDefinitionCache);
        //dmnModelInstanceCache = new DmnModelInstanceCache(factory, cacheCapacity, decisionDefinitionCache);
    }

    public function deploy(DeploymentEntity $deployment): void
    {
        $this->cacheDeployer->deploy($deployment);
    }

    // PROCESS DEFINITION ////////////////////////////////////////////////////////////////////////////////

    public function findProcessDefinitionFromCache(string $processDefinitionId): ?ProcessDefinitionEntity
    {
        return $this->processDefinitionEntityCache->findDefinitionFromCache($processDefinitionId);
    }

    public function findDeployedProcessDefinitionById(string $processDefinitionId): ?ProcessDefinitionEntity
    {
        return $this->processDefinitionEntityCache->findDeployedDefinitionById($processDefinitionId);
    }

    /**
     * @return ProcessDefinitionEntity the latest version of the process definition with the given key (from any tenant)
     * @throws ProcessEngineException if more than one tenant has a process definition with the given key
     * @see #findDeployedLatestProcessDefinitionByKeyAndTenantId(String, String)
     */
    public function findDeployedLatestProcessDefinitionByKey(string $processDefinitionKey): ?ProcessDefinitionEntity
    {
        return $this->processDefinitionEntityCache->findDeployedLatestDefinitionByKey($processDefinitionKey);
    }

    /**
     * @return ProcessDefinitionEntity the latest version of the process definition with the given key and tenant id
     */
    public function findDeployedLatestProcessDefinitionByKeyAndTenantId(string $processDefinitionKey, string $tenantId): ?ProcessDefinitionEntity
    {
        return $this->processDefinitionEntityCache->findDeployedLatestDefinitionByKeyAndTenantId($processDefinitionKey, $tenantId);
    }

    public function findDeployedProcessDefinitionByKeyVersionAndTenantId(string $processDefinitionKey, int $processDefinitionVersion, string $tenantId): ?ProcessDefinitionEntity
    {
        return $this->processDefinitionEntityCache->findDeployedDefinitionByKeyVersionAndTenantId($processDefinitionKey, $processDefinitionVersion, $tenantId);
    }

    public function findDeployedProcessDefinitionByKeyVersionTagAndTenantId(string $processDefinitionKey, string $processDefinitionVersionTag, string $tenantId): ?ProcessDefinitionEntity
    {
        return $this->processDefinitionEntityCache->findDeployedDefinitionByKeyVersionTagAndTenantId($processDefinitionKey, $processDefinitionVersionTag, $tenantId);
    }

    public function findDeployedProcessDefinitionByDeploymentAndKey(string $deploymentId, string $processDefinitionKey): ?ProcessDefinitionEntity
    {
        return $this->processDefinitionEntityCache->findDeployedDefinitionByDeploymentAndKey($deploymentId, $processDefinitionKey);
    }

    public function resolveProcessDefinition(ProcessDefinitionEntity $processDefinition): ?ProcessDefinitionEntity
    {
        return $this->processDefinitionEntityCache->resolveDefinition($processDefinition);
    }

    public function findBpmnModelInstanceForProcessDefinition($processDefinition): ?BpmnModelInstanceInterface
    {
        return $this->bpmnModelInstanceCache->findBpmnModelInstanceForDefinition($processDefinition);
    }

    public function addProcessDefinition(ProcessDefinitionEntity $processDefinition): void
    {
        $this->processDefinitionEntityCache->addDefinition($processDefinition);
    }

    public function removeProcessDefinition(string $processDefinitionId): void
    {
        $this->processDefinitionEntityCache->removeDefinitionFromCache($processDefinitionId);
        $this->bpmnModelInstanceCache->remove($processDefinitionId);
    }

    public function discardProcessDefinitionCache(): void
    {
        $this->processDefinitionEntityCache->clear();
        $this->bpmnModelInstanceCache->clear();
    }

    // FORM DEFINITION ////////////////////////////////////////////////////////////////////////

    /*public void addCamundaFormDefinition(CamundaFormDefinitionEntity camundaFormDefinition) {
        camundaFormDefinitionCache.addDefinition(camundaFormDefinition);
    }

    public void removeCamundaFormDefinition(String camundaFormDefinitionId) {
        camundaFormDefinitionCache.removeDefinitionFromCache(camundaFormDefinitionId);
    }

    public void discardCamundaFormDefinitionCache() {
        camundaFormDefinitionCache.clear();
    }

    // CASE DEFINITION ////////////////////////////////////////////////////////////////////////////////

    public CaseDefinitionEntity findCaseDefinitionFromCache(String caseDefinitionId) {
        return caseDefinitionCache->findDefinitionFromCache(caseDefinitionId);
    }

    public CaseDefinitionEntity findDeployedCaseDefinitionById(String caseDefinitionId) {
        return caseDefinitionCache->findDeployedDefinitionById(caseDefinitionId);
    }*/

    /*public CaseDefinitionEntity findDeployedLatestCaseDefinitionByKey(String caseDefinitionKey) {
        return caseDefinitionCache->findDeployedLatestDefinitionByKey(caseDefinitionKey);
    }*/

    /*public CaseDefinitionEntity findDeployedLatestCaseDefinitionByKeyAndTenantId(String caseDefinitionKey, String tenantId) {
        return caseDefinitionCache->findDeployedLatestDefinitionByKeyAndTenantId(caseDefinitionKey, tenantId);
    }

    public CaseDefinitionEntity findDeployedCaseDefinitionByKeyVersionAndTenantId(String caseDefinitionKey, Integer caseDefinitionVersion, String tenantId) {
        return caseDefinitionCache->findDeployedDefinitionByKeyVersionAndTenantId(caseDefinitionKey, caseDefinitionVersion, tenantId);
    }

    public CaseDefinitionEntity findDeployedCaseDefinitionByDeploymentAndKey(string $deploymentId, String caseDefinitionKey) {
        return caseDefinitionCache->findDeployedDefinitionByDeploymentAndKey($deploymentId, caseDefinitionKey);
    }

    public CaseDefinitionEntity getCaseDefinitionById(String caseDefinitionId) {
        return caseDefinitionCache.getCaseDefinitionById(caseDefinitionId);
    }

    public CaseDefinitionEntity resolveCaseDefinition(CaseDefinitionEntity caseDefinition) {
        return caseDefinitionCache->resolveDefinition(caseDefinition);
    }

    public CmmnModelInstance findCmmnModelInstanceForCaseDefinition(String caseDefinitionId) {
        return cmmnModelInstanceCache->findBpmnModelInstanceForDefinition(caseDefinitionId);
    }

    public void addCaseDefinition(CaseDefinitionEntity caseDefinition) {
        caseDefinitionCache.addDefinition(caseDefinition);
    }

    public void removeCaseDefinition(String caseDefinitionId) {
        caseDefinitionCache.removeDefinitionFromCache(caseDefinitionId);
        cmmnModelInstanceCache.remove(caseDefinitionId);
    }

    public void discardCaseDefinitionCache() {
        caseDefinitionCache.clear();
        cmmnModelInstanceCache.clear();
    }*/

    // DECISION DEFINITION ////////////////////////////////////////////////////////////////////////////

    /*public DecisionDefinitionEntity findDecisionDefinitionFromCache(String decisionDefinitionId) {
        return decisionDefinitionCache->findDefinitionFromCache(decisionDefinitionId);
    }

    public DecisionDefinitionEntity findDeployedDecisionDefinitionById(String decisionDefinitionId) {
        return decisionDefinitionCache->findDeployedDefinitionById(decisionDefinitionId);
    }

    public DecisionDefinition findDeployedLatestDecisionDefinitionByKey(String decisionDefinitionKey) {
        return decisionDefinitionCache->findDeployedLatestDefinitionByKey(decisionDefinitionKey);
    }

    public DecisionDefinition findDeployedLatestDecisionDefinitionByKeyAndTenantId(String decisionDefinitionKey, String tenantId) {
        return decisionDefinitionCache->findDeployedLatestDefinitionByKeyAndTenantId(decisionDefinitionKey, tenantId);
    }

    public DecisionDefinition findDeployedDecisionDefinitionByDeploymentAndKey(string $deploymentId, String decisionDefinitionKey) {
        return decisionDefinitionCache->findDeployedDefinitionByDeploymentAndKey($deploymentId, decisionDefinitionKey);
    }

    public DecisionDefinition findDeployedDecisionDefinitionByKeyAndVersion(String decisionDefinitionKey, Integer decisionDefinitionVersion) {
        return decisionDefinitionCache->findDeployedDefinitionByKeyAndVersion(decisionDefinitionKey, decisionDefinitionVersion);
    }

    public DecisionDefinition findDeployedDecisionDefinitionByKeyVersionAndTenantId(String decisionDefinitionKey, Integer decisionDefinitionVersion, String tenantId) {
        return decisionDefinitionCache->findDeployedDefinitionByKeyVersionAndTenantId(decisionDefinitionKey, decisionDefinitionVersion, tenantId);
    }

    public DecisionDefinition findDeployedDecisionDefinitionByKeyVersionTagAndTenantId(String decisionDefinitionKey, String decisionDefinitionVersionTag, String tenantId) {
        return decisionDefinitionCache->findDeployedDefinitionByKeyVersionTagAndTenantId(decisionDefinitionKey, decisionDefinitionVersionTag, tenantId);
    }

    public DecisionDefinitionEntity resolveDecisionDefinition(DecisionDefinitionEntity decisionDefinition) {
        return decisionDefinitionCache->resolveDefinition(decisionDefinition);
    }

    public DmnModelInstance findDmnModelInstanceForDecisionDefinition(String decisionDefinitionId) {
        return dmnModelInstanceCache->findBpmnModelInstanceForDefinition(decisionDefinitionId);
    }

    public void addDecisionDefinition(DecisionDefinitionEntity decisionDefinition) {
        decisionDefinitionCache.addDefinition(decisionDefinition);
    }

    public void removeDecisionDefinition(String decisionDefinitionId) {
        decisionDefinitionCache.removeDefinitionFromCache(decisionDefinitionId);
        dmnModelInstanceCache.remove(decisionDefinitionId);
    }

    public void discardDecisionDefinitionCache() {
        decisionDefinitionCache.clear();
        dmnModelInstanceCache.clear();
    }*/

    //DECISION REQUIREMENT DEFINITION ////////////////////////////////////////////////////////////////////////////

    /*public void addDecisionRequirementsDefinition(DecisionRequirementsDefinitionEntity decisionRequirementsDefinition) {
        decisionRequirementsDefinitionCache.addDefinition(decisionRequirementsDefinition);
    }

    public DecisionRequirementsDefinitionEntity findDecisionRequirementsDefinitionFromCache(String decisionRequirementsDefinitionId) {
        return decisionRequirementsDefinitionCache->findDefinitionFromCache(decisionRequirementsDefinitionId);
    }

    public DecisionRequirementsDefinitionEntity findDeployedDecisionRequirementsDefinitionById(String decisionRequirementsDefinitionId) {
        return decisionRequirementsDefinitionCache->findDeployedDefinitionById(decisionRequirementsDefinitionId);
    }

    public DecisionRequirementsDefinitionEntity resolveDecisionRequirementsDefinition(DecisionRequirementsDefinitionEntity decisionRequirementsDefinition) {
        return decisionRequirementsDefinitionCache->resolveDefinition(decisionRequirementsDefinition);
    }

    public void discardDecisionRequirementsDefinitionCache() {
        decisionDefinitionCache.clear();
    }

    public void removeDecisionRequirementsDefinition(String decisionRequirementsDefinitionId) {
        decisionRequirementsDefinitionCache.removeDefinitionFromCache(decisionRequirementsDefinitionId);
    }*/

    // getters and setters //////////////////////////////////////////////////////

    public function getBpmnModelInstanceCache(): CacheInterface
    {
        return $this->bpmnModelInstanceCache->getCache();
    }

    /*public Cache<String, CmmnModelInstance> getCmmnModelInstanceCache() {
        return cmmnModelInstanceCache.getCache();
    }

    public Cache<String, DmnModelInstance> getDmnDefinitionCache() {
        return dmnModelInstanceCache.getCache();
    }

    public Cache<String, DecisionDefinitionEntity> getDecisionDefinitionCache() {
        return decisionDefinitionCache.getCache();
    }

    public Cache<String, DecisionRequirementsDefinitionEntity> getDecisionRequirementsDefinitionCache() {
        return decisionRequirementsDefinitionCache.getCache();
    }*/

    public function getProcessDefinitionCache(): CacheInterface
    {
        return $this->processDefinitionEntityCache->getCache();
    }

    /*public function getCaseDefinitionCache() {
        return caseDefinitionCache.getCache();
    }*/

    public function setDeployers(array $deployers): void
    {
        $this->cacheDeployer->setDeployers($deployers);
    }

    public function removeDeployment(string $deploymentId): void
    {
        $this->bpmnModelInstanceCache->removeAllDefinitionsByDeploymentId($deploymentId);
        /*if (Context::getProcessEngineConfiguration().isCmmnEnabled()) {
            cmmnModelInstanceCache.removeAllDefinitionsByDeploymentId($deploymentId);
        }
        if (Context.getProcessEngineConfiguration().isDmnEnabled()) {
            dmnModelInstanceCache.removeAllDefinitionsByDeploymentId($deploymentId);
            removeAllDecisionRequirementsDefinitionsByDeploymentId($deploymentId);
        }*/
    }

    /*protected function removeAllDecisionRequirementsDefinitionsByDeploymentId(string $deploymentId): void
    {
        // remove all decision requirements definitions for a specific deployment
        List<DecisionRequirementsDefinition> allDefinitionsForDeployment = new DecisionRequirementsDefinitionQueryImpl()
            .deploymentId($deploymentId)
            .list();

        for (DecisionRequirementsDefinition decisionRequirementsDefinition : allDefinitionsForDeployment) {
            try {
            removeDecisionDefinition(decisionRequirementsDefinition.getId());
            } catch (Exception e) {
            ProcessEngineLogger.PERSISTENCE_LOGGER
                .removeEntryFromDeploymentCacheFailure("decision requirement", decisionRequirementsDefinition.getId(), e);
            }
        }
    }*/

    public function purgeCache(): CachePurgeReport
    {
        $result = new CachePurgeReport();
        $processDefinitionCache = $this->getProcessDefinitionCache();
        if (!$processDefinitionCache->isEmpty()) {
            $result->addPurgeInformation(CachePurgeReport::PROCESS_DEF_CACHE, $processDefinitionCache->keySet());
            $processDefinitionCache->clear();
        }

        $bpmnModelInstanceCache = $this->getBpmnModelInstanceCache();
        if (!$bpmnModelInstanceCache->isEmpty()) {
            $result->addPurgeInformation(CachePurgeReport::BPMN_MODEL_INST_CACHE, $bpmnModelInstanceCache->keySet());
            $bpmnModelInstanceCache->clear();
        }

       /* Cache<String, CaseDefinitionEntity> caseDefinitionCache = getCaseDefinitionCache();
        if (!caseDefinitionCache.isEmpty()) {
            result.addPurgeInformation(CachePurgeReport.CASE_DEF_CACHE, caseDefinitionCache.keySet());
            caseDefinitionCache.clear();
        }

        Cache<String, CmmnModelInstance> cmmnModelInstanceCache = getCmmnModelInstanceCache();
        if (!cmmnModelInstanceCache.isEmpty()) {
            result.addPurgeInformation(CachePurgeReport.CASE_MODEL_INST_CACHE, cmmnModelInstanceCache.keySet());
            cmmnModelInstanceCache.clear();
        }

        Cache<String, DecisionDefinitionEntity> decisionDefinitionCache = getDecisionDefinitionCache();
        if (!decisionDefinitionCache.isEmpty()) {
            result.addPurgeInformation(CachePurgeReport.DMN_DEF_CACHE, decisionDefinitionCache.keySet());
            decisionDefinitionCache.clear();
        }

        Cache<String, DmnModelInstance> dmnModelInstanceCache = getDmnDefinitionCache();
        if (!dmnModelInstanceCache.isEmpty()) {
            result.addPurgeInformation(CachePurgeReport.DMN_MODEL_INST_CACHE, dmnModelInstanceCache.keySet());
            dmnModelInstanceCache.clear();
        }

        Cache<String, DecisionRequirementsDefinitionEntity> decisionRequirementsDefinitionCache = getDecisionRequirementsDefinitionCache();
        if (!decisionRequirementsDefinitionCache.isEmpty()) {
            result.addPurgeInformation(CachePurgeReport.DMN_REQ_DEF_CACHE, decisionRequirementsDefinitionCache.keySet());
            decisionRequirementsDefinitionCache.clear();
        }*/

        return $result;
    }
}
