<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\Authorization\{
    AuthorizationInterface,
    GroupsInterface,
    HistoricProcessInstancePermissions,
    HistoricTaskPermissions,
    MissingAuthorization,
    PermissionInterface,
    Permissions,
    ProcessDefinitionPermissions,
    ResourceInterface,
    Resources,
    TaskPermissions
};
use Jabe\Engine\{
    AuthorizationException,
    ProcessEngineConfiguration
};
use Jabe\Engine\Impl\{
    AbstractQuery,
    ActivityStatisticsQueryImpl,
    AuthorizationQueryImpl,
    DeploymentQueryImpl,
    DeploymentStatisticsQueryImpl,
    EventSubscriptionQueryImpl,
    ExternalTaskQueryImpl,
    HistoricActivityInstanceQueryImpl,
    //HistoricDecisionInstanceQueryImpl,
    HistoricDetailQueryImpl,
    HistoricExternalTaskLogQueryImpl,
    HistoricIdentityLinkLogQueryImpl,
    HistoricIncidentQueryImpl,
    HistoricJobLogQueryImpl,
    HistoricProcessInstanceQueryImpl,
    HistoricTaskInstanceQueryImpl,
    HistoricVariableInstanceQueryImpl,
    IncidentQueryImpl,
    JobDefinitionQueryImpl,
    JobQueryImpl,
    ProcessDefinitionQueryImpl,
    ProcessDefinitionStatisticsQueryImpl,
    ProcessEngineLogger,
    TaskQueryImpl,
    UserOperationLogQueryImpl,
    VariableInstanceQueryImpl
};
use Jabe\Engine\Impl\Batch\{
    BatchQueryImpl,
    BatchStatisticsQueryImpl
};
use Jabe\Engine\Impl\Batch\History\HistoricBatchQueryImpl;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Db\{
    AuthorizationCheck,
    CompositePermissionCheck,
    DbEntityInterface,
    EnginePersistenceLogger,
    ListQueryParameterObject,
    PermissionCheck,
    PermissionCheckBuilder
};
use Jabe\Engine\Impl\Db\EntityManager\Operation\DbOperation;
use Jabe\Engine\Impl\Identity\Authentication;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Impl\Persistence\AbstractManager;
use Jabe\Engine\Impl\Persistence\Entity\Util\{
    AuthManagerUtil,
    VariablePermissions
};
use Jabe\Engine\Impl\Util\ResourceTypeUtil;
use Jabe\Engine\Query\QueryInterface;

class AuthorizationManager extends AbstractManager
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    // Used instead of Collections.emptyList() as mybatis uses reflection to call methods
    // like size() which can lead to problems as Collections.EmptyList is a private implementation
    protected const EMPTY_LIST = [];

    /**
     * Group ids for which authorizations exist in the database.
     * This is initialized once per command by the {@link #filterAuthenticatedGroupIds(List)} method. (Manager
     * instances are command scoped).
     * It is used to only check authorizations for groups for which authorizations exist. In other words,
     * if for a given group no authorization exists in the DB, then auth checks are not performed for this group.
     */
    protected $availableAuthorizedGroupIds = [];

    protected $isRevokeAuthCheckUsed = null;

    public function newPermissionCheckBuilder(): PermissionCheckBuilder
    {
        return new PermissionCheckBuilder();
    }

    public function createNewAuthorization(int $type): AuthorizationInterface
    {
        $this->checkAuthorization(Permissions::create(), Resources::authorization(), null);
        return new AuthorizationEntity($type);
    }

    public function insert(DbEntityInterface $authorization): void
    {
        $this->checkAuthorization(Permissions::create(), Resources::authorization(), null);
        $this->getDbEntityManager()->insert($authorization);
    }

    public function selectAuthorizationByQueryCriteria(AuthorizationQueryImpl $authorizationQuery): array
    {
        $this->configureQuery($authorizationQuery, Resources::authorization());
        return $this->getDbEntityManager()->selectList("selectAuthorizationByQueryCriteria", $authorizationQuery);
    }

    public function selectAuthorizationCountByQueryCriteria(AuthorizationQueryImpl $authorizationQuery): int
    {
        $this->configureQuery($authorizationQuery, Resources::authorization());
        return $this->getDbEntityManager()->selectOne("selectAuthorizationCountByQueryCriteria", $authorizationQuery);
    }

    public function findAuthorizationByUserIdAndResourceId(int $type, string $userId, ResourceInterface $resource, string $resourceId): ?AuthorizationEntity
    {
        return $this->findAuthorization($type, $userId, null, $resource, $resourceId);
    }

    public function findAuthorizationByGroupIdAndResourceId(int $type, string $groupId, ResourceInterface $resource, string $resourceId): ?AuthorizationEntity
    {
        return $this->findAuthorization($type, null, $groupId, $resource, $resourceId);
    }

    public function findAuthorization(int $type, ?string $userId, ?string $groupId, ?ResourceInterface $resource, string $resourceId): ?AuthorizationEntity
    {
        $params = [];

        $params["type"] = $type;
        $params["userId"] = $userId;
        $params["groupId"] = $groupId;
        $params["resourceId"] = $resourceId;

        if ($resource !== null) {
            $params["resourceType"] = $resource->resourceType();
        }

        return $this->getDbEntityManager()->selectOne("selectAuthorizationByParameters", $params);
    }

    public function update(AuthorizationEntity $authorization): void
    {
        $this->checkAuthorization(Permissions::update(), Resources::authorization(), $authorization->getId());
        $this->getDbEntityManager()->merge($authorization);
    }

    public function delete(DbEntityInterface $authorization): void
    {
        $this->checkAuthorization(Permissions::delete(), Resources::authorization(), $authorization->getId());
        $this->deleteAuthorizationsByResourceId(Resources::authorization(), $authorization->getId());
        parent::delete($authorization);
    }

    // authorization checks ///////////////////////////////////////////

    public function checkAuthorization($permission, ?ResourceInterface $resource = null, ?string $resourceId = null): void
    {
        if ($permission instanceof CompositePermissionCheck) {
            if ($this->isAuthCheckExecuted()) {
                $currentAuthentication = $this->getCurrentAuthentication();
                $userId = $currentAuthentication->getUserId();

                $isAuthorized = $this->isAuthorized(null, [], $permission);
                if (!$this->isAuthorized) {
                    $missingAuthorizations = [];

                    foreach ($permission->getAllPermissionChecks() as $check) {
                        $missingAuthorizations[] = new MissingAuthorization(
                            $check->getPermission()->getName(),
                            $check->getResource()->resourceName(),
                            $check->getResourceId()
                        );
                    }

                    throw new AuthorizationException($userId, $missingAuthorizations);
                }
            }
        } else {
            if ($this->isAuthCheckExecuted()) {
                $currentAuthentication = $this->getCurrentAuthentication();
                $isAuthorized = $this->isAuthorized($currentAuthentication->getUserId(), $currentAuthentication->getGroupIds(), $permission, $resource, $resourceId);
                if (!$isAuthorized) {
                    throw new AuthorizationException(
                        $currentAuthentication->getUserId(),
                        $permission->getName(),
                        $resource->resourceName(),
                        $resourceId
                    );
                }
            }
        }
    }

    public function isAuthorized(
        ?string $userId,
        array $groupIds,
        $permission,
        ?ResourceInterface $resource = null,
        ?string $resourceId = null
    ): bool {
        if ($userId === null) {
            $currentAuthentication = $this->getCurrentAuthentication();
            if ($currentAuthentication === null) {
                return true;
            }
            $userId = $currentAuthentication->getUserId();
            $groupIds = $currentAuthentication->getGroupIds();
        }
        if ($permission instanceof PermissionInterface) {
            if (!$this->isPermissionDisabled($permission)) {
                $permCheck = new PermissionCheck();
                $permCheck->setPermission($permission);
                $permCheck->setResource($resource);
                $permCheck->setResourceId($resourceId);

                return $this->isAuthorized($userId, $groupIds, $permCheck);
            } else {
                return true;
            }
        } elseif ($permission instanceof PermissionCheck) {
            if (!$this->isAuthorizationEnabled()) {
                return true;
            }

            if (!$this->isResourceValidForPermission($permissionCheck)) {
                //throw LOG.invalidResourceForPermission(permissionCheck.getResource().resourceName(), permissionCheck.getPermission().getName());
            }

            $filteredGroupIds = $this->filterAuthenticatedGroupIds($groupIds);

            $isRevokeAuthorizationCheckEnabled = $this->isRevokeAuthCheckEnabled($userId, $groupIds);
            $compositePermissionCheck = $this->createCompositePermissionCheck($permissionCheck);
            $authCheck = new AuthorizationCheck($userId, $filteredGroupIds, $compositePermissionCheck, $isRevokeAuthorizationCheckEnabled);
            return $this->getDbEntityManager()->selectBoolean("isUserAuthorizedForResource", $authCheck);
        } elseif ($permission instanceof CompositePermissionCheck) {
            foreach ($permission->getAllPermissionChecks() as $permissionCheck) {
                if (!$this->isResourceValidForPermission($permissionCheck)) {
                    //throw LOG.invalidResourceForPermission(permissionCheck->getResource().resourceName(), permissionCheck->getPermission()->getName());
                }
            }
            $filteredGroupIds = $this->filterAuthenticatedGroupIds($groupIds);
            $isRevokeAuthorizationCheckEnabled = $this->isRevokeAuthCheckEnabled($userId, $groupIds);
            $authCheck = new AuthorizationCheck($userId, $filteredGroupIds, $permission, $isRevokeAuthorizationCheckEnabled);
            return $this->getDbEntityManager()->selectBoolean("isUserAuthorizedForResource", $authCheck);
        }
    }

    protected function isRevokeAuthCheckEnabled(string $userId, array $groupIds): bool
    {
        $isRevokeAuthCheckEnabled = $this->isRevokeAuthCheckUsed;

        if ($this->isRevokeAuthCheckEnabled === null) {
            $configuredMode = Context::getProcessEngineConfiguration()->getAuthorizationCheckRevokes();
            if ($configuredMode !== null) {
                $configuredMode = strtolower($configuredMode);
            }
            if (ProcessEngineConfiguration::AUTHORIZATION_CHECK_REVOKE_ALWAYS == $configuredMode) {
                $isRevokeAuthCheckEnabled = true;
            } elseif (ProcessEngineConfiguration::AUTHORIZATION_CHECK_REVOKE_NEVER == $configuredMode) {
                $isRevokeAuthCheckEnabled = false;
            } else {
                $params = [];
                $params["userId"] = $userId;
                $params["authGroupIds"] = $this->filterAuthenticatedGroupIds($groupIds);
                $isRevokeAuthCheckEnabled = $this->getDbEntityManager()->selectBoolean("selectRevokeAuthorization", $params);
            }
            $this->isRevokeAuthCheckUsed = $isRevokeAuthCheckEnabled;
        }

        return $isRevokeAuthCheckEnabled;
    }

    protected function createCompositePermissionCheck(PermissionCheck $permissionCheck): CompositePermissionCheck
    {
        $compositePermissionCheck = new CompositePermissionCheck();
        $compositePermissionCheck->setAtomicChecks([$permissionCheck]);
        return $compositePermissionCheck;
    }

    protected function isResourceValidForPermission(PermissionCheck $permissionCheck): bool
    {
        $permissionResources = $permissionCheck->getPermission()->getTypes();
        $givenResource = $permissionCheck->getResource();
        return ResourceTypeUtil::resourceIsContainedInArray($givenResource->resourceType(), $permissionResources);
    }

    public function validateResourceCompatibility(AuthorizationEntity $authorization): void
    {
        $resourceType = $authorization->getResourceType();
        $permissionSet = $authorization->getCachedPermissions();

        foreach ($permissionSet as $permission) {
            if (!ResourceTypeUtil::resourceIsContainedInArray($resourceType, $permission->getTypes())) {
                //throw LOG.invalidResourceForAuthorization(resourceType, permission->getName());
            }
        }
    }

    // authorization checks on queries ////////////////////////////////

    public function configureQueryHistoricFinishedInstanceReport(ListQueryParameterObject $query, ResourceInterface $resource): void
    {
        $this->configureQuery($query);

        $compositePermissionCheck = (new PermissionCheckBuilder())
            ->conjunctive()
            ->atomicCheck($resource, "RES.KEY_", Permissions::read())
            ->atomicCheck($resource, "RES.KEY_", Permissions::readHistory())
            ->build();

        $query->getAuthCheck()->setPermissionChecks($compositePermissionCheck);
    }

    public function enableQueryAuthCheck(AuthorizationCheck $authCheck): void
    {
        $authGroupIds = $authCheck->getAuthGroupIds();
        $authUserId = $authCheck->getAuthUserId();

        $authCheck->setAuthorizationCheckEnabled(true);
        $authCheck->setAuthGroupIds($this->filterAuthenticatedGroupIds($authGroupIds));
        $authCheck->setRevokeAuthorizationCheckEnabled($this->isRevokeAuthCheckEnabled($authUserId, $authGroupIds));
    }

    public function configureQuery(
        $query,
        ?ResourceInterface $resource = null,
        ?string $queryParam = "RES.ID_",
        ?PermissionInterface $permission = null
    ): void {
        if ($query instanceof AbstractQuery) {
            if ($resource === null) {
                $authCheck = $query->getAuthCheck();
                $authCheck->clearPermissionChecks();

                if ($this->isAuthCheckExecuted()) {
                    $currentAuthentication = $this->getCurrentAuthentication();
                    $authCheck->setAuthUserId($currentAuthentication->getUserId());
                    $authCheck->setAuthGroupIds($currentAuthentication->getGroupIds());
                    $this->enableQueryAuthCheck($authCheck);
                } else {
                    $authCheck->setAuthorizationCheckEnabled(false);
                    $authCheck->setAuthUserId(null);
                    $authCheck->setAuthGroupIds(null);
                }
            } else {
                $permission = $permission ?? Permissions::read();
                $this->configureQuery($query);
                $permissionCheck = (new PermissionCheckBuilder())
                    ->atomicCheck($resource, $queryParam, $permission)
                    ->build();
                $this->addPermissionCheck($query->getAuthCheck(), $permissionCheck);
            }
        } elseif ($query instanceof ListQueryParameterObject) {
            $authCheck = $query->getAuthCheck();
            $authCheck->clearPermissionChecks();

            if ($this->isAuthCheckExecuted()) {
                $currentAuthentication = $this->getCurrentAuthentication();
                $authCheck->setAuthUserId($currentAuthentication->getUserId());
                $authCheck->setAuthGroupIds($currentAuthentication->getGroupIds());
                $this->enableQueryAuthCheck($authCheck);
            } else {
                $authCheck->setAuthorizationCheckEnabled(false);
                $authCheck->setAuthUserId(null);
                $authCheck->setAuthGroupIds(null);
            }
        }
    }

    public function isPermissionDisabled(PermissionInterface $permission): bool
    {
        $disabledPermissions = $this->getCommandContext()->getProcessEngineConfiguration()->getDisabledPermissions();
        if (!empty($disabledPermissions)) {
            foreach ($disabledPermissions as $disabledPermission) {
                if ($permission->getName() == $disabledPermission) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function addPermissionCheck(AuthorizationCheck $authCheck, CompositePermissionCheck $compositeCheck): void
    {
        $commandContext = $this->getCommandContext();
        if ($this->isAuthorizationEnabled() && $this->getCurrentAuthentication() !== null && $commandContext->isAuthorizationCheckEnabled()) {
            $authCheck->setPermissionChecks($compositeCheck);
        }
    }

    // delete authorizations //////////////////////////////////////////////////

    public function deleteAuthorizationsByResourceIds(
        ResourceInterface $resource,
        ?array $resourceIds = []
    ): void {

        if (empty($resourceIds)) {
            throw new \Exception("Resource ids cannot be empty");
        }

        foreach ($resourceIds as $resourceId) {
            $this->deleteAuthorizationsByResourceId($resource, $resourceId);
        }
    }

    public function deleteAuthorizationsByResourceId(ResourceInterface $resource, ?string $resourceId): void
    {
        if (empty($resourceId)) {
            throw new \Exception("Resource id cannot be null");
        }

        if ($this->isAuthorizationEnabled()) {
            $deleteParams = [];
            $deleteParams["resourceType"] = $resource->resourceType();
            $deleteParams["resourceId"] = $resourceId;
            $this->getDbEntityManager()->delete(AuthorizationEntity::class, "deleteAuthorizationsForResourceId", $deleteParams);
        }
    }

    public function deleteAuthorizationsByResourceIdAndUserId(ResourceInterface $resource, ?string $resourceId, string $userId): void
    {
        if (empty($resourceId)) {
            throw new \Exception("Resource id cannot be null");
        }

        if ($this->isAuthorizationEnabled()) {
            $deleteParams = [];
            $deleteParams["resourceType"] = $resource->resourceType();
            $deleteParams["resourceId"] = $resourceId;
            $deleteParams["userId"] = $userId;
            $this->getDbEntityManager()->delete(AuthorizationEntity::class, "deleteAuthorizationsForResourceId", $deleteParams);
        }
    }

    public function deleteAuthorizationsByResourceIdAndGroupId(ResourceInterface $resource, ?string $resourceId, string $groupId): void
    {
        if (empty($resourceId)) {
            throw new \Exception("Resource id cannot be null");
        }

        if ($this->isAuthorizationEnabled()) {
            $deleteParams = [];
            $deleteParams["resourceType"] = $resource->resourceType();
            $deleteParams["resourceId"] = $resourceId;
            $deleteParams["groupId"] = $groupId;
            $this->getDbEntityManager()->delete(AuthorizationEntity::class, "deleteAuthorizationsForResourceId", $deleteParams);
        }
    }

    // predefined authorization checks

    /**
     * Checks if the current authentication contains the group
     * Groups#ADMIN. The check is ignored if the authorization is
     * disabled or no authentication exists.
     *
     * @throws AuthorizationException
     */
    public function checkAdmin(): void
    {
        $currentAuthentication = $this->getCurrentAuthentication();
        $commandContext = Context::getCommandContext();

        if (
            $this->isAuthorizationEnabled() && $commandContext->isAuthorizationCheckEnabled()
            && $currentAuthentication !== null  && !$this->isAdmin($currentAuthentication)
        ) {
            //throw LOG.requiredCamundaAdminException();
        }
    }

    /**
     * @param authentication
     *          authentication to check, cannot be <code>null</code>
     * @return bool true if the given authentication contains the group
     *         Groups#CAMUNDA_ADMIN or the user
     */
    public function isAdmin(Authentication $authentication): bool
    {
        $groupIds = $authentication->getGroupIds();
        if (!empty($groupIds)) {
            $commandContext = Context::getCommandContext();
            $adminGroups = $commandContext->getProcessEngineConfiguration()->getAdminGroups();
            foreach ($adminGroups as $adminGroup) {
                if (in_array($adminGroup, $groupIds)) {
                    return true;
                }
            }
        }

        $userId = $authentication->getUserId();
        if ($userId !== null) {
            $commandContext = Context::getCommandContext();
            $adminUsers = $commandContext->getProcessEngineConfiguration()->getAdminUsers();
            return !empty($adminUsers) && in_array($userId, $adminUsers);
        }

        return false;
    }

    /* QUERIES */

    // deployment query ////////////////////////////////////////

    public function configureDeploymentQuery(DeploymentQueryImpl $query): void
    {
        $this->configureQuery($query, Resources::deployment());
    }

    // process definition query ////////////////////////////////

    public function configureProcessDefinitionQuery(ProcessDefinitionQueryImpl $query): void
    {
        $this->configureQuery($query, Resources::processDefinition(), "RES.KEY_");

        if ($query->isStartablePermissionCheck()) {
            $authorizationCheck = $query->getAuthCheck();

            if (!$authorizationCheck->isRevokeAuthorizationCheckEnabled()) {
                $permCheck = (new PermissionCheckBuilder())
                    ->atomicCheck(Resources::processDefinition(), "RES.KEY_", Permissions::createInstance())
                    ->build();

                $query->addProcessDefinitionCreatePermissionCheck($permCheck);
            } else {
                $permissionCheck = (new PermissionCheckBuilder())
                    ->conjunctive()
                    ->atomicCheck(Resources::processDefinition(), "RES.KEY_", Permissions::read())
                    ->atomicCheck(Resources::processDefinition(), "RES.KEY_", Permissions::createInstance())
                    ->build();
                $this->addPermissionCheck($authorizationCheck, $permissionCheck);
            }
        }
    }

    // execution/process instance query ////////////////////////

    public function configureExecutionQuery(AbstractQuery $query): void
    {
        $this->configureQuery($query);
        $permissionCheck = (new PermissionCheckBuilder())
            ->disjunctive()
            ->atomicCheck(Resources::processInstance(), "RES.PROC_INST_ID_", Permissions::read())
            ->atomicCheck(Resources::processDefinition(), "P.KEY_", Permissions::readInstance())
            ->build();
        $this->addPermissionCheck($query->getAuthCheck(), $permissionCheck);
    }

    // task query //////////////////////////////////////////////

    public function configureTaskQuery(TaskQueryImpl $query): void
    {
        $this->configureQuery($query);

        if ($query->getAuthCheck()->isAuthorizationCheckEnabled()) {
            // necessary authorization check when the task is part of
            // a running process instance

            $permissionCheck = (new PermissionCheckBuilder())
                    ->disjunctive()
                    ->atomicCheck(Resources::task(), "RES.ID_", Permissions::read())
                    ->atomicCheck(Resources::processDefinition(), "D.KEY_", Permissions::readTask())
                    ->build();
            $this->addPermissionCheck($query->getAuthCheck(), $permissionCheck);
        }
    }

    // event subscription query //////////////////////////////

    public function configureEventSubscriptionQuery(EventSubscriptionQueryImpl $query): void
    {
        $this->configureQuery($query);
        $permissionCheck = (new PermissionCheckBuilder())
                ->disjunctive()
                ->atomicCheck(Resources::processInstance(), "RES.PROC_INST_ID_", Permissions::read())
                ->atomicCheck(Resources::processDefinition(), "PROCDEF.KEY_", Permissions::readInstance())
                ->build();
        $this->addPermissionCheck($query->getAuthCheck(), $permissionCheck);
    }

    public function configureConditionalEventSubscriptionQuery(ListQueryParameterObject $query): void
    {
        $this->configureQuery($query);
        $permissionCheck = (new PermissionCheckBuilder())
            ->atomicCheck(Resources::processDefinition(), "P.KEY_", Permissions::read())
            ->build();
        $this->addPermissionCheck($query->getAuthCheck(), $permissionCheck);
    }

    // incident query ///////////////////////////////////////

    public function configureIncidentQuery(IncidentQueryImpl $query): void
    {
        $this->configureQuery($query);
        $permissionCheck = (new PermissionCheckBuilder())
                ->disjunctive()
                ->atomicCheck(Resources::processInstance(), "RES.PROC_INST_ID_", Permissions::read())
                ->atomicCheck(Resources::processDefinition(), "PROCDEF.KEY_", Permissions::readInstance())
                ->build();
        $this->addPermissionCheck($query->getAuthCheck(), $permissionCheck);
    }

    // variable instance query /////////////////////////////

    protected function configureVariableInstanceQuery(VariableInstanceQueryImpl $query): void
    {
        $this->configureQuery($query);

        if ($query->getAuthCheck()->isAuthorizationCheckEnabled()) {
            $permissionCheck = null;
            if ($this->isEnsureSpecificVariablePermission()) {
                $permissionCheck = (new PermissionCheckBuilder())
                    ->disjunctive()
                    ->atomicCheck(Resources::processDefinition(), "PROCDEF.KEY_", ProcessDefinitionPermissions::readInstanceVariable())
                    ->atomicCheck(Resources::task(), "RES.TASK_ID_", TaskPermissions::readVariable())
                    ->build();
            } else {
                $permissionCheck = (new PermissionCheckBuilder())
                    ->disjunctive()
                    ->atomicCheck(Resources::processInstance(), "RES.PROC_INST_ID_", Permissions::read())
                    ->atomicCheck(Resources::processDefinition(), "PROCDEF.KEY_", Permissions::readInstance())
                    ->atomicCheck(Resources::task(), "RES.TASK_ID_", Permissions::read())
                    ->build();
            }
            $this->addPermissionCheck($query->getAuthCheck(), $permissionCheck);
        }
    }

    // job definition query ////////////////////////////////////////////////

    public function configureJobDefinitionQuery(JobDefinitionQueryImpl $query): void
    {
        $this->configureQuery($query, Resources::processDefinition(), "RES.PROC_DEF_KEY_");
    }

    // job query //////////////////////////////////////////////////////////

    public function configureJobQuery(JobQueryImpl $query): void
    {
        $this->configureQuery($query);
        $permissionCheck = (new PermissionCheckBuilder())
            ->disjunctive()
            ->atomicCheck(Resources::processInstance(), "RES.PROCESS_INSTANCE_ID_", Permissions::read())
            ->atomicCheck(Resources::processDefinition(), "RES.PROCESS_DEF_KEY_", Permissions::readInstance())
            ->build();
        $this->addPermissionCheck($query->getAuthCheck(), $permissionCheck);
    }

    /* HISTORY */

    // historic process instance query ///////////////////////////////////

    public function configureHistoricProcessInstanceQuery(HistoricProcessInstanceQueryImpl $query): void
    {
        $authCheck = $query->getAuthCheck();

        $isHistoricInstancePermissionsEnabled = $this->isHistoricInstancePermissionsEnabled();
        $authCheck->setHistoricInstancePermissionsEnabled($isHistoricInstancePermissionsEnabled);

        if (!$isHistoricInstancePermissionsEnabled) {
            $this->configureQuery($query, Resources::processDefinition(), "SELF.PROC_DEF_KEY_", Permissions::readHistory());
        } else {
            $this->configureQuery($query);

            $permissionCheck = (new PermissionCheckBuilder())
                ->disjunctive()
                ->atomicCheck(Resources::processDefinition(), "SELF.PROC_DEF_KEY_", Permissions::readHistory())
                ->atomicCheck(Resources::historicProcessInstance(), "SELF.ID_", HistoricProcessInstancePermissions::read())
                ->build();

            $this->addPermissionCheck($authCheck, $permissionCheck);
        }
    }

    // historic activity instance query /////////////////////////////////

    public function configureHistoricActivityInstanceQuery(HistoricActivityInstanceQueryImpl $query): void
    {
        $authCheck = $query->getAuthCheck();

        $isHistoricInstancePermissionsEnabled = $this->isHistoricInstancePermissionsEnabled();
        $authCheck->setHistoricInstancePermissionsEnabled($isHistoricInstancePermissionsEnabled);

        if (!$isHistoricInstancePermissionsEnabled) {
            $this->configureQuery($query, Resources::processDefinition(), "RES.PROC_DEF_KEY_", Permissions::readHistory());
        } else {
            $this->configureQuery($query);

            $permissionCheck = (new PermissionCheckBuilder())
                ->disjunctive()
                ->atomicCheck(Resources::processDefinition(), "RES.PROC_DEF_KEY_", Permissions::readHistory())
                ->atomicCheck(
                    Resources::historicProcessInstance(),
                    "RES.PROC_INST_ID_",
                    HistoricProcessInstancePermissions::read()
                )
                ->build();
            $this->addPermissionCheck($authCheck, $permissionCheck);
        }
    }

    // historic task instance query ////////////////////////////////////

    public function configureHistoricTaskInstanceQuery(HistoricTaskInstanceQueryImpl $query): void
    {
        $authCheck = $query->getAuthCheck();

        $isHistoricInstancePermissionsEnabled = $this->isHistoricInstancePermissionsEnabled();
        $authCheck->setHistoricInstancePermissionsEnabled($isHistoricInstancePermissionsEnabled);

        if (!$isHistoricInstancePermissionsEnabled) {
            $this->configureQuery($query, Resources::processDefinition(), "RES.PROC_DEF_KEY_", Permissions::readHistory());
        } else {
            $this->configureQuery($query);

            $permissionCheck = (new PermissionCheckBuilder())
                ->disjunctive()
                ->atomicCheck(Resources::processDefinition(), "RES.PROC_DEF_KEY_", Permissions::readHistory())
                ->atomicCheck(
                    Resources::historicProcessInstance(),
                    "RES.PROC_INST_ID_",
                    HistoricProcessInstancePermissions::read()
                )
                ->atomicCheck(Resources::historicTask(), "RES.ID_", HistoricTaskPermissions::read())
                ->build();

            $this->addPermissionCheck($query->getAuthCheck(), $permissionCheck);
        }
    }

    // historic variable instance query ////////////////////////////////

    public function configureHistoricVariableInstanceQuery(HistoricVariableInstanceQueryImpl $query): void
    {
        $this->configureHistoricVariableAndDetailQuery($query);
    }

    // historic detail query ////////////////////////////////

    public function configureHistoricDetailQuery(HistoricDetailQueryImpl $query): void
    {
        $this->configureHistoricVariableAndDetailQuery($query);
    }

    protected function configureHistoricVariableAndDetailQuery(AbstractQuery $query): void
    {
        $ensureSpecificVariablePermission = $this->isEnsureSpecificVariablePermission();

        $variablePermissions =
            AuthManagerUtil::getVariablePermissions($ensureSpecificVariablePermission);

        $processDefinitionPermission = $variablePermissions->getProcessDefinitionPermission();

        $authCheck = $query->getAuthCheck();

        $isHistoricInstancePermissionsEnabled = $this->isHistoricInstancePermissionsEnabled();
        $authCheck->setHistoricInstancePermissionsEnabled($isHistoricInstancePermissionsEnabled);

        if (!$isHistoricInstancePermissionsEnabled) {
            $this->configureQuery($query, Resources::processDefinition(), "RES.PROC_DEF_KEY_", $processDefinitionPermission);
        } else {
            $this->configureQuery($query);

            $historicTaskPermission = $variablePermissions->getHistoricTaskPermission();

            $permissionCheck = (new PermissionCheckBuilder())
                ->disjunctive()
                ->atomicCheck(Resources::processDefinition(), "RES.PROC_DEF_KEY_", $processDefinitionPermission)
                ->atomicCheck(
                    Resources::historicProcessInstance(),
                    "RES.PROC_INST_ID_",
                    HistoricProcessInstancePermissions::read()
                )
                ->atomicCheck(Resources::historicTask(), "TI.ID_", $historicTaskPermission)
                ->build();

            $this->addPermissionCheck($authCheck, $permissionCheck);
        }
    }

    // historic job log query ////////////////////////////////

    public function configureHistoricJobLogQuery(HistoricJobLogQueryImpl $query): void
    {
        $authCheck = $query->getAuthCheck();

        $isHistoricInstancePermissionsEnabled = $this->isHistoricInstancePermissionsEnabled();
        $authCheck->setHistoricInstancePermissionsEnabled($isHistoricInstancePermissionsEnabled);

        if (!$isHistoricInstancePermissionsEnabled) {
            $this->configureQuery($query, Resources::processDefinition(), "RES.PROCESS_DEF_KEY_", Permissions::readHistory());
        } else {
            $this->configureQuery($query);
            $permissionCheck = (new PermissionCheckBuilder())
                ->disjunctive()
                ->atomicCheck(Resources::processDefinition(), "RES.PROCESS_DEF_KEY_", Permissions::readHistory())
                ->atomicCheck(
                    Resources::historicProcessInstance(),
                    "RES.PROCESS_INSTANCE_ID_",
                    HistoricProcessInstancePermissions::read()
                )
                ->build();
            $this->addPermissionCheck($authCheck, $permissionCheck);
        }
    }

    // historic incident query ////////////////////////////////

    public function configureHistoricIncidentQuery(HistoricIncidentQueryImpl $query): void
    {
        $authCheck = $query->getAuthCheck();

        $isHistoricInstancePermissionsEnabled = $this->isHistoricInstancePermissionsEnabled();
        $authCheck->setHistoricInstancePermissionsEnabled($isHistoricInstancePermissionsEnabled);

        if (!$isHistoricInstancePermissionsEnabled) {
            $this->configureQuery($query, Resources::processDefinition(), "RES.PROC_DEF_KEY_", Permissions::readHistory());
        } else {
            $this->configureQuery($query);
            $permissionCheck = (new PermissionCheckBuilder())
                ->disjunctive()
                ->atomicCheck(Resources::processDefinition(), "RES.PROC_DEF_KEY_", Permissions::readHistory())
                ->atomicCheck(
                    Resources::historicProcessInstance(),
                    "RES.PROC_INST_ID_",
                    HistoricProcessInstancePermissions::read()
                )
                ->build();
            $this->addPermissionCheck($authCheck, $permissionCheck);
        }
    }

    //historic identity link query ////////////////////////////////

    public function configureHistoricIdentityLinkQuery(HistoricIdentityLinkLogQueryImpl $query): void
    {
        $authCheck = $query->getAuthCheck();

        $isHistoricInstancePermissionsEnabled = $this->isHistoricInstancePermissionsEnabled();
        $authCheck->setHistoricInstancePermissionsEnabled($isHistoricInstancePermissionsEnabled);

        if (!$isHistoricInstancePermissionsEnabled) {
            $this->configureQuery($query, Resources::processDefinition(), "RES.PROC_DEF_KEY_", Permissions::readHistory());
        } else {
            $this->configureQuery($query);

            $permissionCheck = (new PermissionCheckBuilder())
                ->disjunctive()
                ->atomicCheck(Resources::processDefinition(), "RES.PROC_DEF_KEY_", Permissions::readHistory())
                ->atomicCheck(
                    Resources::historicProcessInstance(),
                    "TI.PROC_INST_ID_",
                    HistoricProcessInstancePermissions::read()
                )
                ->atomicCheck(
                    Resources::historicTask(),
                    "RES.TASK_ID_",
                    HistoricTaskPermissions::read()
                )
                ->build();

            $this->addPermissionCheck($authCheck, $permissionCheck);
        }
    }

    public function configureHistoricDecisionInstanceQuery(HistoricDecisionInstanceQueryImpl $query): void
    {
        $this->configureQuery($query, Resources::decisionDefinition(), "RES.DEC_DEF_KEY_", Permissions::readHistory());
    }

    // historic external task log query /////////////////////////////////

    public function configureHistoricExternalTaskLogQuery(HistoricExternalTaskLogQueryImpl $query): void
    {
        $authCheck = $query->getAuthCheck();

        $isHistoricInstancePermissionsEnabled = $this->isHistoricInstancePermissionsEnabled();
        $authCheck->setHistoricInstancePermissionsEnabled($isHistoricInstancePermissionsEnabled);

        if (!$this->isHistoricInstancePermissionsEnabled) {
            $this->configureQuery($query, Resources::processDefinition(), "RES.PROC_DEF_KEY_", Permissions::readHistory());
        } else {
            $this->configureQuery($query);

            $permissionCheck = (new PermissionCheckBuilder())
                ->disjunctive()
                ->atomicCheck(Resources::processDefinition(), "RES.PROC_DEF_KEY_", Permissions::readHistory())
                ->atomicCheck(
                    Resources::historicProcessInstance(),
                    "RES.PROC_INST_ID_",
                    HistoricProcessInstancePermissions::read()
                )
                ->build();
            $this->addPermissionCheck($authCheck, $permissionCheck);
        }
    }

    // user operation log query ///////////////////////////////
    public function configureUserOperationLogQuery(UserOperationLogQueryImpl $query): void
    {
        $this->configureQuery($query);
        $permissionCheckBuilder = (new PermissionCheckBuilder())
            ->disjunctive()
            ->atomicCheck(Resources::processDefinition(), "RES.PROC_DEF_KEY_", Permissions::readHistory())
            ->atomicCheck(Resources::operationLogCategory(), "RES.CATEGORY_", Permissions::read());

        $authCheck = $query->getAuthCheck();

        $isHistoricInstancePermissionsEnabled = $this->isHistoricInstancePermissionsEnabled();
        $authCheck->setHistoricInstancePermissionsEnabled($isHistoricInstancePermissionsEnabled);

        if ($isHistoricInstancePermissionsEnabled) {
            $permissionCheckBuilder
                ->atomicCheck(
                    Resources::historicProcessInstance(),
                    "RES.PROC_INST_ID_",
                    HistoricProcessInstancePermissions::read()
                )
                ->atomicCheck(
                    Resources::historicTask(),
                    "RES.TASK_ID_",
                    HistoricTaskPermissions::read()
                );
        }
        $permissionCheck = $permissionCheckBuilder->build();
        $this->addPermissionCheck($authCheck, $permissionCheck);
    }

    // batch

    public function configureHistoricBatchQuery(HistoricBatchQueryImpl $query): void
    {
        $this->configureQuery($query, Resources::batch(), "RES.ID_", Permissions::readHistory());
    }

    /* STATISTICS QUERY */
    public function configureDeploymentStatisticsQuery(DeploymentStatisticsQueryImpl $query): void
    {
        $this->configureQuery($query, Resources::deployment(), "RES.ID_");

        $query->clearProcessInstancePermissionChecks();
        $query->clearJobPermissionChecks();
        $query->clearIncidentPermissionChecks();

        if ($query->getAuthCheck()->isAuthorizationCheckEnabled()) {
            $processInstancePermissionCheck = (new PermissionCheckBuilder())
                ->disjunctive()
                ->atomicCheck(Resources::processInstance(), "EXECUTION.PROC_INST_ID_", Permissions::read())
                ->atomicCheck(Resources::processDefinition(), "PROCDEF.KEY_", Permissions::readInstance())
                ->build();

            $query->addProcessInstancePermissionCheck($processInstancePermissionCheck->getAllPermissionChecks());

            if ($query->isFailedJobsToInclude()) {
                $jobPermissionCheck = (new PermissionCheckBuilder())
                    ->disjunctive()
                    ->atomicCheck(Resources::processInstance(), "JOB.PROCESS_INSTANCE_ID_", Permissions::read())
                    ->atomicCheck(Resources::processDefinition(), "JOB.PROCESS_DEF_KEY_", Permissions::readInstance())
                    ->build();

                $query->addJobPermissionCheck($jobPermissionCheck->getAllPermissionChecks());
            }

            if ($query->isIncidentsToInclude()) {
                $incidentPermissionCheck = (new PermissionCheckBuilder())
                    ->disjunctive()
                    ->atomicCheck(Resources::processInstance(), "INC.PROC_INST_ID_", Permissions::read())
                    ->atomicCheck(Resources::processDefinition(), "PROCDEF.KEY_", Permissions::readInstance())
                    ->build();

                $query->addIncidentPermissionCheck($incidentPermissionCheck->getAllPermissionChecks());
            }
        }
    }

    public function configureProcessDefinitionStatisticsQuery(ProcessDefinitionStatisticsQueryImpl $query): void
    {
        $this->configureQuery($query, Resources::processDefinition(), "RES.KEY_");
    }

    public function configureActivityStatisticsQuery(ActivityStatisticsQueryImpl $query): void
    {
        $this->configureQuery($query);

        $query->clearPermissionChecks();
        $query->clearJobPermissionChecks();
        $query->clearIncidentPermissionChecks();

        if ($query->getAuthCheck()->isAuthorizationCheckEnabled()) {
            $processInstancePermissionCheck = (new PermissionCheckBuilder())
                ->disjunctive()
                ->atomicCheck(Resources::processInstance(), "E.PROC_INST_ID_", Permissions::read())
                ->atomicCheck(Resources::processDefinition(), "P.KEY_", Permissions::readInstance())
                ->build();

            // the following is need in order to evaluate whether to perform authCheck or not
            $query->getAuthCheck()->setPermissionChecks($processInstancePermissionCheck);
            // the actual check
            $query->addProcessInstancePermissionCheck($processInstancePermissionCheck->getAllPermissionChecks());

            if ($query->isFailedJobsToInclude()) {
                $jobPermissionCheck = (new PermissionCheckBuilder())
                    ->disjunctive()
                    ->atomicCheck(Resources::processInstance(), "JOB.PROCESS_INSTANCE_ID_", Permissions::read())
                    ->atomicCheck(Resources::processDefinition(), "JOB.PROCESS_DEF_KEY_", Permissions::readInstance())
                    ->build();

                // the following is need in order to evaluate whether to perform authCheck or not
                $query->getAuthCheck()->setPermissionChecks($jobPermissionCheck);
                // the actual check
                $query->addJobPermissionCheck($jobPermissionCheck->getAllPermissionChecks());
            }

            if ($query->isIncidentsToInclude()) {
                $incidentPermissionCheck = (new PermissionCheckBuilder())
                    ->disjunctive()
                    ->atomicCheck(Resources::processInstance(), "I.PROC_INST_ID_", Permissions::read())
                    ->atomicCheck(Resources::processDefinition(), "PROCDEF.KEY_", Permissions::readInstance())
                    ->build();

                // the following is need in order to evaluate whether to perform authCheck or not
                $query->getAuthCheck()->setPermissionChecks($incidentPermissionCheck);
                // the actual check
                $query->addIncidentPermissionCheck($incidentPermissionCheck->getAllPermissionChecks());
            }
        }
    }

    public function configureExternalTaskQuery(ExternalTaskQueryImpl $query): void
    {
        $this->configureQuery($query);
        $permissionCheck = (new PermissionCheckBuilder())
            ->disjunctive()
            ->atomicCheck(Resources::processInstance(), "RES.PROC_INST_ID_", Permissions::read())
            ->atomicCheck(Resources::processDefinition(), "RES.PROC_DEF_KEY_", Permissions::readInstance())
            ->build();
        $this->addPermissionCheck($query->getAuthCheck(), $permissionCheck);
    }

    public function configureExternalTaskFetch(ListQueryParameterObject $parameter): void
    {
        $this->configureQuery($parameter);

        $permissionCheck = (new PermissionCheckBuilder())
            ->conjunctive()
            ->composite()
            ->disjunctive()
            ->atomicCheck(Resources::processInstance(), "RES.PROC_INST_ID_", Permissions::read())
            ->atomicCheck(Resources::processDefinition(), "RES.PROC_DEF_KEY_", Permissions::readInstance())
            ->done()
            ->composite()
            ->disjunctive()
            ->atomicCheck(Resources::processInstance(), "RES.PROC_INST_ID_", Permissions::update())
            ->atomicCheck(Resources::processDefinition(), "RES.PROC_DEF_KEY_", Permissions::updateInstance())
            ->done()
            ->build();

        $this->addPermissionCheck($parameter->getAuthCheck(), $permissionCheck);
    }

    public function configureDecisionDefinitionQuery(DecisionDefinitionQueryImpl $query): void
    {
        $this->configureQuery($query, Resources::decisionDefinition(), "RES.KEY_");
    }

    public function configureDecisionRequirementsDefinitionQuery(DecisionRequirementsDefinitionQueryImpl $query): void
    {
        $this->configureQuery($query, Resources::decisionRequirementsDefinition(), "RES.KEY_");
    }

    public function configureBatchQuery(BatchQueryImpl $query): void
    {
        $this->configureQuery($query, Resources::batch(), "RES.ID_", Permissions::read());
    }

    public function configureBatchStatisticsQuery(BatchStatisticsQueryImpl $query): void
    {
        $this->configureQuery($query, Resources::batch(), "RES.ID_", Permissions::read());
    }

    public function filterAuthenticatedGroupIds(?array $authenticatedGroupIds = []): array
    {
        if (empty($authenticatedGroupIds)) {
            return self::EMPTY_LIST;
        } else {
            if (empty($this->availableAuthorizedGroupIds)) {
                $availableAuthorizedGroupIds = $this->getDbEntityManager()->selectList("selectAuthorizedGroupIds");
            }
            return $availableAuthorizedGroupIds;
        }
    }

    protected function isAuthCheckExecuted(): bool
    {
        $currentAuthentication = $this->getCurrentAuthentication();
        $commandContext = Context::getCommandContext();
        return $this->isAuthorizationEnabled()
            && $commandContext->isAuthorizationCheckEnabled()
            && $currentAuthentication !== null
            && $currentAuthentication->getUserId() !== null;
    }

    public function isEnsureSpecificVariablePermission(): bool
    {
        return Context::getProcessEngineConfiguration()->isEnforceSpecificVariablePermission();
    }

    protected function isHistoricInstancePermissionsEnabled(): bool
    {
        return Context::getProcessEngineConfiguration()->isEnableHistoricInstancePermissions();
    }

    public function addRemovalTimeToAuthorizationsByRootProcessInstanceId(
        string $rootProcessInstanceId,
        string $removalTime
    ): void {
        $parameters = [];
        $parameters["rootProcessInstanceId"] = $rootProcessInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(
                AuthorizationEntity::class,
                "updateAuthorizationsByRootProcessInstanceId",
                $parameters
            );
    }

    public function addRemovalTimeToAuthorizationsByProcessInstanceId(
        string $processInstanceId,
        string $removalTime
    ): void {
        $parameters = [];
        $parameters["processInstanceId"] = $processInstanceId;
        $parameters["removalTime"] = $removalTime;

        $this->getDbEntityManager()
            ->updatePreserveOrder(
                AuthorizationEntity::class,
                "updateAuthorizationsByProcessInstanceId",
                $parameters
            );
    }

    public function deleteAuthorizationsByRemovalTime(
        string $removalTime,
        int $minuteFrom,
        int $minuteTo,
        int $batchSize
    ): DbOperation {
        $parameters = [];
        $parameters["removalTime"] = $removalTime;
        if ($minuteTo - $minuteFrom + 1 < 60) {
            $parameters["minuteFrom"] = $minuteFrom;
            $parameters["minuteTo"] = $minuteTo;
        }
        $parameters["batchSize"] = $batchSize;

        return $this->getDbEntityManager()
            ->deletePreserveOrder(
                AuthorizationEntity::class,
                "deleteAuthorizationsByRemovalTime",
                new ListQueryParameterObject($parameters, 0, $batchSize)
            );
    }
}
