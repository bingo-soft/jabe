<?php

namespace Jabe\Impl\Bpmn\Deployer;

use Jabe\Impl\{
    AbstractDefinitionDeployer,
    ProcessEngineLogger
};
use Jabe\Impl\Bpmn\Helper\BpmnProperties;
use Jabe\Impl\Bpmn\Parser\{
    BpmnParse,
    BpmnParser,
    BpmnParseLogger,
    EventSubscriptionDeclaration
};
use Jabe\Impl\Cmd\DeleteJobsCmd;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Core\Model\{
    Properties,
    PropertyMapKey
};
use Jabe\Impl\Db\EntityManager\DbEntityManager;
use Jabe\Impl\El\ExpressionManager;
use Jabe\Impl\Event\EventType;
use Jabe\Impl\JobExecutor\{
    JobDeclaration,
    TimerDeclarationImpl,
    TimerStartEventJobHandler
};
use Jabe\Impl\Persistence\Deploy\Cache\DeploymentCache;
use Jabe\Impl\Persistence\Entity\{
    DeploymentEntity,
    EventSubscriptionEntity,
    EventSubscriptionManager,
    IdentityLinkEntity,
    JobDefinitionEntity,
    JobDefinitionManager,
    JobEntity,
    JobManager,
    ProcessDefinitionEntity,
    ProcessDefinitionManager,
    ResourceEntity
};
use Jabe\Impl\Pvm\Runtime\LegacyBehavior;
use Jabe\Repository\ProcessDefinitionInterface;
use Jabe\Task\IdentityLinkType;

class BpmnDeployer extends AbstractDefinitionDeployer
{
    //public static BpmnParseLogger LOG = ProcessEngineLogger.BPMN_PARSE_LOGGER;

    public const BPMN_RESOURCE_SUFFIXES = [ "bpmn20.xml", "bpmn" ];

    protected static $JOB_DECLARATIONS_PROPERTY;

    protected $expressionManager;
    protected $bpmnParser;

    /** <!> DON'T KEEP DEPLOYMENT-SPECIFIC STATE <!> **/

    public function __construct()
    {
        if (self::$JOB_DECLARATIONS_PROPERTY === null) {
            self::$JOB_DECLARATIONS_PROPERTY = new PropertyMapKey("JOB_DECLARATIONS_PROPERTY");
        }
    }

    protected function getResourcesSuffixes(): array
    {
        return self::BPMN_RESOURCE_SUFFIXES;
    }

    protected function transformDefinitions(DeploymentEntity $deployment, ResourceEntity $resource, Properties $properties): array
    {
        $bytes = $resource->getBytes();

        $inputStream = tmpfile();
        fwrite($inputStream, $bytes);

        $bpmnParse = $this->bpmnParser
            ->createParse()
            ->sourceInputStream($inputStream)
            ->deployment($deployment)
            ->name($resource->getName());

        if (!$deployment->isValidatingSchema()) {
            $bpmnParse->setSchemaResource(null);
        }

        $bpmnParse->execute();

        if (!$this->properties->contains(self::$JOB_DECLARATIONS_PROPERTY)) {
            $this->properties->set(self::$JOB_DECLARATIONS_PROPERTY, []);
        }
        $this->properties->set(self::$JOB_DECLARATIONS_PROPERTY, array_merge($this->properties->get(self::$JOB_DECLARATIONS_PROPERTY), $bpmnParse->getJobDeclarations()));

        return $bpmnParse->getProcessDefinitions();
    }

    protected function findDefinitionByDeploymentAndKey(string $deploymentId, string $definitionKey): ProcessDefinitionEntity
    {
        return $this->getProcessDefinitionManager()->findProcessDefinitionByDeploymentAndKey($deploymentId, $definitionKey);
    }

    protected function findLatestDefinitionByKeyAndTenantId(string $definitionKey, ?string $tenantId): ProcessDefinitionEntity
    {
        return $this->getProcessDefinitionManager()->findLatestProcessDefinitionByKeyAndTenantId($definitionKey, $tenantId);
    }

    protected function persistDefinition(ProcessDefinitionEntity $definition): void
    {
        getProcessDefinitionManager()->insertProcessDefinition($definition);
    }

    protected function addDefinitionToDeploymentCache(DeploymentCache $deploymentCache, ProcessDefinitionEntity $definition): void
    {
        $deploymentCache->addProcessDefinition($definition);
    }

    protected function definitionAddedToDeploymentCache(DeploymentEntity $deployment, ProcessDefinitionEntity $definition, Properties $properties): void
    {
        $props = $properties->get(self::$JOB_DECLARATIONS_PROPERTY);
        $declarations = [];
        if (array_key_exists($definition->getKey(), $props)) {
            $declarations = $props[$definition->getKey()];
        }

        $this->updateJobDeclarations($declarations, $definition, $deployment->isNew());

        $latestDefinition = $this->findLatestDefinitionByKeyAndTenantId($definition->getKey(), $definition->getTenantId());

        if ($deployment->isNew()) {
            $this->adjustStartEventSubscriptions($definition, $latestDefinition);
        }

        // add "authorizations"
        $this->addAuthorizations($definition);
    }

    protected function persistedDefinitionLoaded(DeploymentEntity $deployment, ProcessDefinitionEntity $definition, ProcessDefinitionEntity $persistedDefinition): void
    {
        $definition->setSuspensionState($persistedDefinition->getSuspensionState());
    }

    protected function handlePersistedDefinition(ProcessDefinitionEntity $definition, ?ProcessDefinitionEntity $persistedDefinition, DeploymentEntity $deployment, Properties $properties): void
    {
        //check if persisted definition is not null, since the process definition can be deleted by the user
        //in such cases we don't want to handle them
        //we can't do this in the parent method, since other siblings want to handle them like DecisionDefinitionDeployer
        if ($persistedDefinition !== null) {
            parent::handlePersistedDefinition($definition, $persistedDefinition, $deployment, $properties);
        }
    }

    protected function updateJobDeclarations(array $jobDeclarations, ProcessDefinitionEntity $processDefinition, bool $isNewDeployment): void
    {
        if (empty($jobDeclarations)) {
            return;
        }

        $jobDefinitionManager = $this->getJobDefinitionManager();

        if ($isNewDeployment) {
            // create new job definitions:
            foreach ($jobDeclarations as $jobDeclaration) {
                $this->createJobDefinition($processDefinition, $jobDeclaration);
            }
        }/* else {
            // query all job definitions and update the declarations with their Ids
            List<JobDefinitionEntity> existingDefinitions = jobDefinitionManager->findByProcessDefinitionId(processDefinition->getId());

            LegacyBehavior.migrateMultiInstanceJobDefinitions(processDefinition, existingDefinitions);

            for (JobDeclaration<?, ?> jobDeclaration : jobDeclarations) {
                boolean jobDefinitionExists = false;
                for (JobDefinition jobDefinitionEntity : existingDefinitions) {

                    // <!> Assumption: there can be only one job definition per activity and type
                    if (jobDeclaration->getActivityId().equals(jobDefinitionEntity->getActivityId()) &&
                        jobDeclaration->getJobHandlerType().equals(jobDefinitionEntity->getJobType())) {
                        jobDeclaration.setJobDefinitionId(jobDefinitionEntity->getId());
                        jobDefinitionExists = true;
                        break;
                    }
                }

                if (!jobDefinitionExists) {
                    // not found: create new definition
                    createJobDefinition(processDefinition, jobDeclaration);
                }

            }
        }*/
    }

    protected function createJobDefinition(ProcessDefinitionInterface $processDefinition, JobDeclaration $jobDeclaration): void
    {
        $jobDefinitionManager = $this->getJobDefinitionManager();

        $jobDefinitionEntity = new JobDefinitionEntity($jobDeclaration);
        $jobDefinitionEntity->setProcessDefinitionId($processDefinition->getId());
        $jobDefinitionEntity->setProcessDefinitionKey($processDefinition->getKey());
        $jobDefinitionEntity->setTenantId($processDefinition->getTenantId());
        $jobDefinitionManager->insert($jobDefinitionEntity);
        $jobDeclaration->setJobDefinitionId($jobDefinitionEntity->getId());
    }

    /**
     * adjust all event subscriptions responsible to start process instances
     * (timer start event, message start event). The default behavior is to remove the old
     * subscriptions and add new ones for the new deployed process definitions.
     */
    protected function adjustStartEventSubscriptions(ProcessDefinitionEntity $newLatestProcessDefinition, ProcessDefinitionEntity $oldLatestProcessDefinition): void
    {
        $this->removeObsoleteTimers($newLatestProcessDefinition);
        $this->addTimerDeclarations($newLatestProcessDefinition);

        $this->removeObsoleteEventSubscriptions($newLatestProcessDefinition, $oldLatestProcessDefinition);
        $this->addEventSubscriptions($newLatestProcessDefinition);
    }

    protected function addTimerDeclarations(ProcessDefinitionEntity $processDefinition): void
    {
        $timerDeclarations = $processDefinition->getProperty(BpmnParse::PROPERTYNAME_START_TIMER);
        if ($timerDeclarations !== null) {
            foreach ($timerDeclarations as $timerDeclaration) {
                $deploymentId = $processDefinition->getDeploymentId();
                $timerDeclaration->createStartTimerInstance($deploymentId);
            }
        }
    }

    protected function removeObsoleteTimers(ProcessDefinitionEntity $processDefinition): void
    {
        $jobsToDelete = $this->getJobManager()
            ->findJobsByConfiguration(TimerStartEventJobHandler::TYPE, $processDefinition->getKey(), $processDefinition->getTenantId());

        foreach ($jobsToDelete as $job) {
            (new DeleteJobsCmd($job->getId()))->execute(Context::getCommandContext());
        }
    }

    protected function removeObsoleteEventSubscriptions(ProcessDefinitionEntity $processDefinition, ProcessDefinitionEntity $latestProcessDefinition): void
    {
        // remove all subscriptions for the previous version
        if ($latestProcessDefinition !== null) {
            $eventSubscriptionManager = $this->getEventSubscriptionManager();

            $subscriptionsToDelete = [];

            $messageEventSubscriptions = $eventSubscriptionManager->findEventSubscriptionsByConfiguration(EventType::message()->name(), $latestProcessDefinition->getId());
            $subscriptionsToDelete = array_merge($subscriptionsToDelete, $messageEventSubscriptions);

            $signalEventSubscriptions = $eventSubscriptionManager->findEventSubscriptionsByConfiguration(EventType::signal()->name(), $latestProcessDefinition->getId());
            $subscriptionsToDelete = array_merge($subscriptionsToDelete, $signalEventSubscriptions);

            $conditionalEventSubscriptions = $eventSubscriptionManager->findEventSubscriptionsByConfiguration(EventType::conditional()->name(), $latestProcessDefinition->getId());
            $subscriptionsToDelete = array_merge($subscriptionsToDelete, $conditionalEventSubscriptions);

            foreach ($subscriptionsToDelete as $eventSubscriptionEntity) {
                $eventSubscriptionEntity->delete();
            }
        }
    }

    public function addEventSubscriptions(ProcessDefinitionEntity $processDefinition): void
    {
        $eventDefinitions = $processDefinition->getProperties()->get(BpmnProperties::eventSubscriptionDeclarations());
        foreach (array_values($eventDefinitions) as $eventDefinition) {
            $this->addEventSubscription($processDefinition, $eventDefinition);
        }
    }

    protected function addEventSubscription(ProcessDefinitionEntity $processDefinition, EventSubscriptionDeclaration $eventDefinition): void
    {
        if ($eventDefinition->isStartEvent()) {
            $eventType = $eventDefinition->getEventType();

            if ($eventType == EventType::message()->name()) {
                $this->addMessageStartEventSubscription($eventDefinition, $processDefinition);
            } elseif ($eventType == EventType::signal()->name()) {
                $this->addSignalStartEventSubscription($eventDefinition, $processDefinition);
            } elseif ($eventType == EventType::conditional()->name()) {
                $this->addConditionalStartEventSubscription($eventDefinition, $processDefinition);
            }
        }
    }

    protected function addMessageStartEventSubscription(EventSubscriptionDeclaration $messageEventDefinition, ProcessDefinitionEntity $processDefinition): void
    {
        $tenantId = $processDefinition->getTenantId();

        if ($this->isSameMessageEventSubscriptionAlreadyPresent($messageEventDefinition, $tenantId)) {
            //throw LOG.messageEventSubscriptionWithSameNameExists(processDefinition->getResourceName(), messageEventDefinition->getUnresolvedEventName());
            throw new \Exception("messageEventSubscriptionWithSameNameExists");
        }

        $newSubscription = $messageEventDefinition->createSubscriptionForStartEvent($processDefinition);
        $newSubscription->insert();
    }

    protected function isSameMessageEventSubscriptionAlreadyPresent(EventSubscriptionDeclaration $eventSubscription, string $tenantId): bool
    {
        // look for subscriptions for the same name in db:
        $subscriptionsForSameMessageName = $this->getEventSubscriptionManager()
            ->findEventSubscriptionsByNameAndTenantId(EventType::message()->name(), $eventSubscription->getUnresolvedEventName(), $tenantId);

        // also look for subscriptions created in the session:
        $cachedSubscriptions = $this->getDbEntityManager()->getCachedEntitiesByType(EventSubscriptionEntity::class);

        foreach ($cachedSubscriptions as $cachedSubscription) {
            if (
                $eventSubscription->getUnresolvedEventName() == $cachedSubscription->getEventName()
                && $this->hasTenantId($cachedSubscription, $tenantId)
                && !in_array($cachedSubscription, $subscriptionsForSameMessageName)
            ) {
                $subscriptionsForSameMessageName[] = $cachedSubscription;
            }
        }

        // remove subscriptions deleted in the same command
        $subscriptionsForSameMessageName = $this->getDbEntityManager()->pruneDeletedEntities($subscriptionsForSameMessageName);

        // remove subscriptions for different type of event (i.e. remove intermediate message event subscriptions)
        $subscriptionsForSameMessageName = $this->filterSubscriptionsOfDifferentType($eventSubscription, $subscriptionsForSameMessageName);

        return !empty($subscriptionsForSameMessageName);
    }

    protected function hasTenantId(EventSubscriptionEntity $cachedSubscription, ?string $tenantId): bool
    {
        if ($tenantId === null) {
            return $cachedSubscription->getTenantId() === null;
        } else {
            return $tenantId == $cachedSubscription->getTenantId();
        }
    }

    /**
     * It is possible to deploy a process containing a start and intermediate
     * message event that wait for the same message or to have two processes, one
     * with a message start event and the other one with a message intermediate
     * event, that subscribe for the same message. Therefore we have to find out
     * if there are subscriptions for the other type of event and remove those.
     *
     * @param eventSubscription
     * @param subscriptionsForSameMessageName
     */
    protected function filterSubscriptionsOfDifferentType(EventSubscriptionDeclaration $eventSubscription, array $subscriptionsForSameMessageName): array
    {
        $filteredSubscriptions = $subscriptionsForSameMessageName ;

        foreach ($subscriptionsForSameMessageName as $subscriptionEntity) {
            if ($this->isSubscriptionOfDifferentTypeAsDeclaration($subscriptionEntity, $eventSubscription)) {
                foreach ($filteredSubscriptions as $key => $value) {
                    if ($value == $subscriptionEntity) {
                        unset($filteredSubscriptions[$subscriptionEntity]);
                    }
                }
            }
        }

        return $filteredSubscriptions;
    }

    protected function isSubscriptionOfDifferentTypeAsDeclaration(EventSubscriptionEntity $subscriptionEntity, EventSubscriptionDeclaration $declaration): bool
    {
        return ($declaration->isStartEvent() && $this->isSubscriptionForIntermediateEvent($subscriptionEntity))
            || (!$declaration->isStartEvent() && $this->isSubscriptionForStartEvent($subscriptionEntity));
    }

    protected function isSubscriptionForStartEvent(EventSubscriptionEntity $subscriptionEntity): bool
    {
        return $subscriptionEntity->getExecutionId() === null;
    }

    protected function isSubscriptionForIntermediateEvent(EventSubscriptionEntity $subscriptionEntity): bool
    {
        return $subscriptionEntity->getExecutionId() !== null;
    }

    protected function addSignalStartEventSubscription(EventSubscriptionDeclaration $signalEventDefinition, ProcessDefinitionEntity $processDefinition): void
    {
        $newSubscription = $signalEventDefinition->createSubscriptionForStartEvent($processDefinition);
        $newSubscription->insert();
    }

    protected function addConditionalStartEventSubscription(EventSubscriptionDeclaration $conditionalEventDefinition, ProcessDefinitionEntity $processDefinition): void
    {
        $newSubscription = $conditionalEventDefinition->createSubscriptionForStartEvent($processDefinition);
        $newSubscription->insert();
    }

    protected function addAuthorizationsFromIterator(array $exprSet, ProcessDefinitionEntity $processDefinition, string $exprType): void
    {
        if (!empty($exprSet)) {
            foreach ($exprSet as $expr) {
                $identityLink = new IdentityLinkEntity();
                $identityLink->setProcessDef($processDefinition);
                if ($exprType == ExprType::USER) {
                    $identityLink->setUserId(strval($expr));
                } elseif ($exprType == ExprType::GROUP) {
                    $identityLink->setGroupId(strval($expr));
                }
                $identityLink->setType(IdentityLinkType::CANDIDATE);
                $identityLink->setTenantId($processDefinition->getTenantId());
                $identityLink->insert();
            }
        }
    }

    protected function addAuthorizations(ProcessDefinitionEntity $processDefinition): void
    {
        $this->addAuthorizationsFromIterator($processDefinition->getCandidateStarterUserIdExpressions(), $processDefinition, ExprType::USER);
        $this->addAuthorizationsFromIterator($processDefinition->getCandidateStarterGroupIdExpressions(), $processDefinition, ExprType::GROUP);
    }

    // context ///////////////////////////////////////////////////////////////////////////////////////////

    protected function getDbEntityManager(): DbEntityManager
    {
        return $this->getCommandContext()->getDbEntityManager();
    }

    protected function getJobManager(): JobManager
    {
        return $this->getCommandContext()->getJobManager();
    }

    protected function getJobDefinitionManager(): JobDefinitionManager
    {
        return $this->getCommandContext()->getJobDefinitionManager();
    }

    protected function getEventSubscriptionManager(): EventSubscriptionManager
    {
        return $this->getCommandContext()->getEventSubscriptionManager();
    }

    protected function getProcessDefinitionManager(): ProcessDefinitionManager
    {
        return $this->getCommandContext()->getProcessDefinitionManager();
    }

    // getters/setters ///////////////////////////////////////////////////////////////////////////////////

    public function getExpressionManager(): ExpressionManager
    {
        return $this->expressionManager;
    }

    public function setExpressionManager(ExpressionManager $expressionManager): void
    {
        $this->expressionManager = $expressionManager;
    }

    public function getBpmnParser(): BpmnParser
    {
        return $this->bpmnParser;
    }

    public function setBpmnParser(BpmnParser $bpmnParser): void
    {
        $this->bpmnParser = $bpmnParser;
    }
}
