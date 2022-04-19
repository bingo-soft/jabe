<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    ActivityInstanceImpl,
    ExecutionEntity,
    IncidentEntity,
    TransitionInstanceImpl
};
use BpmPlatform\Engine\Impl\Pvm\Process\{
    ActivityImpl,
    ScopeImpl
};
use BpmPlatform\Engine\Impl\Pvm\Runtime\{
    CompensationBehavior,
    LegacyBehavior,
    PvmExecutionImpl
};
use BpmPlatform\Engine\Impl\Util\{
    CollectionUtil,
    EnsureUtil
};
use BpmPlatform\Engine\Runtime\{
    ActivityInstanceInterface,
    IncidentInterface
};

class GetActivityInstanceCmd implements CommandInterface
{
    protected $processInstanceId;

    public function __construct(string $processInstanceId)
    {
        $this->processInstanceId = $processInstanceId;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("processInstanceId", "processInstanceId", $this->processInstanceId);
        $executionList = $this->loadProcessInstance($this->processInstanceId, $commandContext);

        if (empty($executionList)) {
            return null;
        }

        $this->checkGetActivityInstance($this->processInstanceId, $commandContext);

        $nonEventScopeExecutions = $this->filterNonEventScopeExecutions($executionList);
        $leaves = $this->filterLeaves($nonEventScopeExecutions);
        // Leaves must be ordered in a predictable way (e.g. by ID)
        // in order to return a stable execution tree with every repeated invocation of this command.
        // For legacy process instances, there may miss scope executions for activities that are now a scope.
        // In this situation, there may be multiple scope candidates for the same instance id; which one
        // can depend on the order the leaves are iterated.
        $this->orderById($leaves);

        $processInstance = $this->filterProcessInstance($executionList);

        if ($processInstance->isEnded()) {
            return null;
        }

        $incidents = $this->groupIncidentIdsByExecutionId($commandContext);

        // create act instance for process instance
        $processActInst = $this->createActivityInstance(
            $processInstance,
            $processInstance->getProcessDefinition(),
            $this->processInstanceId,
            null,
            $incidents
        );

        $activityInstances = [];
        $activityInstances[$this->processInstanceId] = $processActInst;

        $transitionInstances = [];

        foreach ($leaves as $leaf) {
            // skip leafs without activity, e.g. if only the process instance exists after cancellation
            // it will not have an activity set
            if ($leaf->getActivity() == null) {
                continue;
            }

            $activityExecutionMapping = $leaf->createActivityExecutionMapping();
            $scopeInstancesToCreate = $activityExecutionMapping;

            // create an activity/transition instance for each leaf that executes a non-scope activity
            // and does not throw compensation
            if ($leaf->getActivityInstanceId() != null) {
                if (!CompensationBehavior::isCompensationThrowing($leaf) || LegacyBehavior::isCompensationThrowing($leaf, $activityExecutionMapping)) {
                    $parentActivityInstanceId = null;
                    foreach ($activityExecutionMapping as $pair) {
                        if ($pair[0] == $leaf->getActivity()->getFlowScope()) {
                            $parentActivityInstanceId = $pair[1]->getParentActivityInstanceId();
                            break;
                        }
                    }

                    $leafInstance = $this->createActivityInstance(
                        $leaf,
                        $leaf->getActivity(),
                        $leaf->getActivityInstanceId(),
                        $parentActivityInstanceId,
                        $incidents
                    );
                    $activityInstances[$leafInstance->getId()] = $leafInstance;

                    $actToRemove = $leaf->getActivity();
                    foreach ($scopeInstancesToCreate as $key => $pair) {
                        if ($pair[0] == $actToRemove) {
                            unset($scopeInstancesToCreate[$key]);
                            break;
                        }
                    }
                }
            } else {
                $transitionInstance = $this->createTransitionInstance($leaf, $incidents);
                $transitionInstances[$transitionInstance->getId()] = $transitionInstance;
                $actToRemove = $leaf->getActivity();
                foreach ($scopeInstancesToCreate as $key => $pair) {
                    if ($pair[0] == $actToRemove) {
                        unset($scopeInstancesToCreate[$key]);
                        break;
                    }
                }
            }

            LegacyBehavior::removeLegacyNonScopesFromMapping($scopeInstancesToCreate);
            $actToRemove = $leaf->getProcessDefinition();
            foreach ($scopeInstancesToCreate as $key => $pair) {
                if ($pair[0] == $actToRemove) {
                    unset($scopeInstancesToCreate[$key]);
                    break;
                }
            }

            // create an activity instance for each scope (including compensation throwing executions)
            foreach ($scopeInstancesToCreate as $pair) {
                $scope = $pair[0];
                $scopeExecution = $pair[1];

                $activityInstanceId = null;
                $parentActivityInstanceId = null;

                $activityInstanceId = $scopeExecution->getParentActivityInstanceId();

                foreach ($activityExecutionMapping as $pair) {
                    if ($pair[0] == $scope->getFlowScope()) {
                        $parentActivityInstanceId = $pair[1]->getParentActivityInstanceId();
                        break;
                    }
                }

                if (array_key_exists($activityInstanceId, $activityInstances)) {
                    continue;
                } else {
                    // regardless of the tree structure (compacted or not), the scope's activity instance id
                    // is the activity instance id of the parent execution and the parent activity instance id
                    // of that is the actual parent activity instance id
                    $scopeInstance = $this->createActivityInstance(
                        $scopeExecution,
                        $scope,
                        $activityInstanceId,
                        $parentActivityInstanceId,
                        $incidents
                    );
                    $activityInstances[$activityInstanceId] = $scopeInstance;
                }
            }
        }

        LegacyBehavior::repairParentRelationships(array_values($activityInstances), $this->processInstanceId);
        $this->populateChildInstances($activityInstances, $transitionInstances);

        return $processActInst;
    }

    protected function checkGetActivityInstance(string $processInstanceId, CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadProcessInstance($processInstanceId);
        }
    }

    protected function orderById(array &$leaves): void
    {
        usort($leaves, function (ExecutionEntity $o1, ExecutionEntity $o2) {
            return ($o1->getId() < $o2->getId()) ? -1 : 1;
        });
    }

    protected function createActivityInstance(
        PvmExecutionImpl $scopeExecution,
        ScopeImpl $scope,
        string $activityInstanceId,
        string $parentActivityInstanceId,
        array $incidentsByExecution
    ): ActivityInstanceImpl {
        $actInst = new ActivityInstanceImpl();

        $actInst->setId($activityInstanceId);
        $actInst->setParentActivityInstanceId($parentActivityInstanceId);
        $actInst->setProcessInstanceId($scopeExecution->getProcessInstanceId());
        $actInst->setProcessDefinitionId($scopeExecution->getProcessDefinitionId());
        $actInst->setBusinessKey($scopeExecution->getBusinessKey());
        $actInst->setActivityId($scope->getId());

        $name = $scope->getName();
        if ($name == null) {
            $name = $scope->getProperty("name");
        }
        $actInst->setActivityName($name);

        if ($scope->getId() == $scopeExecution->getProcessDefinition()->getId()) {
            $actInst->setActivityType("processDefinition");
        } else {
            $actInst->setActivityType($scope->getProperty("type"));
        }

        $executionIds = [];
        $incidentIds = [];
        $incidents = [];

        $executionIds[] = $scopeExecution->getId();

        $executionActivity = $scopeExecution->getActivity();

        // do not collect incidents if scopeExecution is a compacted subtree
        // and we currently create the scope activity instance
        if ($executionActivity == null || $executionActivity == $scope) {
            $incidentIds = array_merge($incidentIds, $this->getIncidentIds($incidentsByExecution, $scopeExecution));
            $incidents = array_merge($incidents, $this->getIncidents($incidentsByExecution, $scopeExecution));
        }

        foreach ($scopeExecution->getNonEventScopeExecutions() as $childExecution) {
            // add all concurrent children that are not in an activity
            if ($childExecution->isConcurrent() && $childExecution->getActivityId() == null) {
                $executionIds[] = $childExecution->getId();
                $incidentIds = array_merge($incidentIds, $this->getIncidentIds($incidentsByExecution, $childExecution));
                $incidents = array_merge($incidents, $this->getIncidents($incidentsByExecution, $childExecution));
            }
        }

        $actInst->setExecutionIds($executionIds);
        $actInst->setIncidentIds($incidentIds);
        $actInst->setIncidents($incidents);

        return $actInst;
    }

    protected function createTransitionInstance(
        PvmExecutionImpl $execution,
        array $incidentsByExecution
    ): TransitionInstanceImpl {
        $transitionInstance = new TransitionInstanceImpl();

        // can use execution id as persistent ID for transition as an execution
        // can execute as most one transition at a time.
        $transitionInstance->setId($execution->getId());
        $transitionInstance->setParentActivityInstanceId($execution->getParentActivityInstanceId());
        $transitionInstance->setProcessInstanceId($execution->getProcessInstanceId());
        $transitionInstance->setProcessDefinitionId($execution->getProcessDefinitionId());
        $transitionInstance->setExecutionId($execution->getId());
        $transitionInstance->setActivityId($activityInstanceIdexecution->getActivityId());

        $activity = $execution->getActivity();
        if ($activity != null) {
            $name = $activity->getName();
            if ($name == null) {
                $name = $activity->getProperty("name");
            }
            $transitionInstance->setActivityName($name);
            $transitionInstance->setActivityType($activity->getProperty("type"));
        }

        $incidentIdList = $this->getIncidentIds($incidentsByExecution, $execution);
        $incidents = $this->getIncidents($incidentsByExecution, $execution);
        $transitionInstance->setIncidentIds($incidentIdList);
        $transitionInstance->setIncidents($incidents);

        return $transitionInstance;
    }

    protected function populateChildInstances(
        array $activityInstances,
        array $transitionInstances
    ): void {
        $childActivityInstances = [];
        $childTransitionInstances = [];

        foreach (array_values($activityInstances) as $instance) {
            if ($instance->getParentActivityInstanceId() != null) {
                $key = $instance->getParentActivityInstanceId();
                $parentInstance = null;
                if (array_key_exists($key, $activityInstances)) {
                    $parentInstance = $activityInstances[$key];
                }
                if ($parentInstance == null) {
                    throw new ProcessEngineException("No parent activity instance with id " . $instance->getParentActivityInstanceId() . " generated");
                }
                $this->putListElement($childActivityInstances, $parentInstance, $instance);
            }
        }

        foreach (array_values($transitionInstances) as $instance) {
            if ($instance->getParentActivityInstanceId() != null) {
                $key = $instance->getParentActivityInstanceId();
                $parentInstance = null;
                if (array_key_exists($key, $activityInstances)) {
                    $parentInstance = $activityInstances[$key];
                }
                if ($parentInstance == null) {
                    throw new ProcessEngineException("No parent activity instance with id " . $instance->getParentActivityInstanceId() . " generated");
                }
                $this->putListElement($childTransitionInstances, $parentInstance, $instance);
            }
        }

        foreach ($childActivityInstances as $pair) {
            $instance = $pair[0];
            $childInstances = $pair[1];
            if (!empty($childInstances)) {
                $instance->setChildActivityInstances($childInstances);
            }
        }

        foreach ($childTransitionInstances as $pair) {
            $instance = $pair[0];
            $childInstances = $pair[1];
            if (!empty($childTransitionInstances)) {
                $instance->setChildTransitionInstances($childInstances);
            }
        }
    }

    protected function putListElement(array &$mapOfLists, $key, $listElement): void
    {
        $exists = false;
        foreach ($mapOfLists as $idx => $pair) {
            if ($pair[0] == $key) {
                $exists = true;
                $mapOfLists[$idx] = [$key, array_merge($pair[1], [$listElement])];
                break;
            }
        }
        if (!$exists) {
            $mapOfLists[] = [$key, [$listElement]];
        }
    }

    protected function filterProcessInstance(array $executionList): ExecutionEntity
    {
        foreach ($executionList as $execution) {
            if ($execution->isProcessInstanceExecution()) {
                return $execution;
            }
        }
        throw new ProcessEngineException("Could not determine process instance execution");
    }

    protected function filterLeaves(array $executionList): array
    {
        $leaves = [];
        foreach ($executionList as $execution) {
            // although executions executing throwing compensation events are not leaves in the tree,
            // they are treated as leaves since their child executions are logical children of their parent scope execution
            if (empty($execution->getNonEventScopeExecutions()) || CompensationBehavior::isCompensationThrowing($execution)) {
                $leaves[] = $execution;
            }
        }
        return $leaves;
    }

    protected function filterNonEventScopeExecutions(array $executionList): array
    {
        $nonEventScopeExecutions = [];
        foreach ($executionList as $execution) {
            if (!$execution->isEventScope()) {
                $nonEventScopeExecutions[] = $execution;
            }
        }
        return $nonEventScopeExecutions;
    }

    protected function loadProcessInstance(string $processInstanceId, CommandContext $commandContext): array
    {
        $result = null;

        // first try to load from cache
        // check whether the process instance is already (partially) loaded in command context
        $cachedExecutions = $commandContext->getDbEntityManager()->getCachedEntitiesByType(ExecutionEntity::class);
        foreach ($cachedExecutions as $executionEntity) {
            if ($this->processInstanceId == $executionEntity->getProcessInstanceId()) {
                // found one execution from process instance
                $result = [];
                $processInstance = $executionEntity->getProcessInstance();
                // add process instance
                $result[] = $processInstance;
                $this->loadChildExecutionsFromCache($processInstance, $result);
                break;
            }
        }

        if (empty($result)) {
            // if the process instance could not be found in cache, load from database
            $result = $this->loadFromDb($processInstanceId, $commandContext);
        }

        return $result;
    }

    protected function loadFromDb(string $processInstanceId, CommandContext $commandContext): array
    {
        $executions = $commandContext->getExecutionManager()->findExecutionsByProcessInstanceId($processInstanceId);
        $processInstance = $commandContext->getExecutionManager()->findExecutionById($processInstanceId);

        // initialize parent/child sets
        if ($processInstance != null) {
            $processInstance->restoreProcessInstance($executions, null, null, null, null, null, null);
        }

        return $executions;
    }

    /**
     * Loads all executions that are part of this process instance tree from the dbSqlSession cache.
     * (optionally querying the db if a child is not already loaded.
     *
     * @param execution the current root execution (already contained in childExecutions)
     * @param childExecutions the list in which all child executions should be collected
     */
    protected function loadChildExecutionsFromCache(ExecutionEntity $execution, array &$childExecutions): void
    {
        $childrenOfThisExecution = $execution->getExecutions();
        if (!empty($childrenOfThisExecution)) {
            $childExecutions = array_merge($childExecutions, $childrenOfThisExecution);
            foreach ($childrenOfThisExecution as $child) {
                $this->loadChildExecutionsFromCache($child, $childExecutions);
            }
        }
    }

    protected function groupIncidentIdsByExecutionId(CommandContext $commandContext): array
    {
        $incidents = $commandContext->getIncidentManager()->findIncidentsByProcessInstance($this->processInstanceId);
        $result = [];
        foreach ($incidents as $incidentEntity) {
            CollectionUtil::addToMapOfLists($result, $incidentEntity->getExecutionId(), $incidentEntity);
        }
        return $result;
    }

    protected function getIncidentIds(
        array $incidents,
        PvmExecutionImpl $execution
    ): array {
        $incidentIds = [];
        $key = $execution->getId();
        if (array_key_exists($key, $incidents)) {
            $incidentList = $incidents[$key];
        }
        if (!empty($incidentList)) {
            foreach ($incidentList as $incident) {
                $incidentIds[] = $incident->getId();
            }
            return $incidentIds;
        } else {
            return [];
        }
    }

    protected function getIncidents(
        array $incidents,
        PvmExecutionImpl $execution
    ): array {
        $id = $execution->getId();
        if (array_key_exists($id, $incidents)) {
            return $incidents[$id];
        }
        return [];
    }
}
