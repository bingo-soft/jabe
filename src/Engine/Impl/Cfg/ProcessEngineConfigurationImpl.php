<?php

namespace Jabe\Engine\Impl\Cfg;

use Doctrine\DBAL\{
    Configuration,
    Connection,
    DriverManager
};
use Doctrine\DBAL\{
    Query\QueryBuilder,
    Types\Type
};
use Doctrine\ORM\{
    EntityManager,
    Tools\Setup
};
use Jabe\Engine\{
    ArtifactFactoryInterface,
    AuthorizationServiceInterface,
    ExternalTaskServiceInterface,
    FilterServiceInterface,
    FormServiceInterface,
    HistoryServiceInterface,
    IdentityServiceInterface,
    ManagementServiceInterface,
    ProcessEngineInterface,
    ProcessEngineConfiguration,
    ProcessEngineException,
    RepositoryServiceInterface,
    RuntimeServiceInterface,
    TaskServiceInterface
};
use Jabe\Engine\Authorization\{
    GroupsInterface,
    PermissionInterface,
    Permissions
};
use Jabe\Engine\Impl\{
    AuthorizationServiceImpl,
    ExternalTaskServiceImpl,
    FilterServiceImpl,
    FormServiceImpl,
    HistoryServiceImpl,
    IdentityServiceImpl,
    ManagementServiceImpl,
    ModificationBatchJobHandler,
    OptimizeService,
    PriorityProviderInterface,
    ProcessEngineImpl,
    ProcessEngineLogger,
    RepositoryServiceImpl,
    RestartProcessInstancesJobHandler,
    RuntimeServiceImpl,
    ServiceImpl,
    TaskServiceImpl
};
use Jabe\Engine\Variable\SerializationDataFormats;

abstract class ProcessEngineConfigurationImpl extends ProcessEngineConfiguration
{
    protected $defaultSerializationFormat = SerializationDataFormats::PHP;

    public function getDefaultSerializationFormat(): string
    {
        return $this->defaultSerializationFormat;
    }
}
