<?php

namespace Jabe\Impl\Cfg;

use Doctrine\DBAL\{
    Connection,
    Result,
    Statement
};
use Jabe\{
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
use Jabe\Authorization\{
    GroupsInterface,
    PermissionInterface,
    Permissions
};
use Jabe\Impl\{
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
//@TODO
use Jabe\Impl\Application\ProcessApplicationManager;
use Jabe\Impl\Batch\{
    BatchJobHandlerInterface,
    BatchMonitorJobHandler,
    BatchSeedJobHandler
};
use Jabe\Impl\Batch\Deletion\{
    DeleteHistoricProcessInstancesJobHandler,
    DeleteProcessInstancesJobHandler
};
use Jabe\Impl\Batch\ExternalTask\SetExternalTaskRetriesJobHandler;
use Jabe\Impl\Batch\Job\SetJobRetriesJobHandler;
use Jabe\Impl\Batch\Message\MessageCorrelationBatchJobHandler;
use Jabe\Impl\Batch\RemovalTime\{
    BatchSetRemovalTimeJobHandler,
    ProcessSetRemovalTimeJobHandler
};
use Jabe\Impl\Batch\Update\UpdateProcessInstancesSuspendStateJobHandler;
use Jabe\Impl\Batch\Variables\BatchSetVariablesHandler;
use Jabe\Impl\Bpmn\Behavior\ExternalTaskActivityBehavior;
use Jabe\Impl\Bpmn\Deployer\BpmnDeployer;
use Jabe\Impl\Bpmn\Parser\{
    BpmnParseListenerInterface,
    BpmnParser,
    DefaultFailedJobParseListener
};
use Jabe\Impl\Calendar\{
    BusinessCalendarManagerInterface,
    CycleBusinessCalendar,
    DueDateBusinessCalendar,
    DurationBusinessCalendar,
    MapBusinessCalendarManager
};
//@TODO
use Jabe\Impl\Cfg\Auth\{
    AuthorizationCommandChecker,
    DefaultAuthorizationProvider,
    DefaultPermissionProvider,
    PermissionProviderInterface
};
//@TODO
use Jabe\Impl\Cfg\Multitenancy\{
    TenantCommandChecker,
    TenantIdProviderInterface
};
//@TODO
use Jabe\Impl\Cfg\Standalone\StandaloneTransactionContextFactory;
use Jabe\Impl\Cmd\{
    HistoryCleanupCmd
};
use Jabe\Impl\Db\DbIdGenerator;
use Jabe\Impl\Db\EntityManager\DbEntityManagerFactory;
use Jabe\Impl\Db\EntityManager\Cache\DbEntityCacheKeyMapping;
use Jabe\Impl\Db\Sql\{
    DbSqlPersistenceProviderFactory,
    DbSqlSessionFactory
};
use Jabe\Impl\Delegate\DefaultDelegateInterceptor;
//@TODO
use Jabe\Impl\Digest\{
    Default16ByteSaltGenerator,
    PasswordEncryptorInterface,
    PasswordManager,
    SaltGeneratorInterface,
    Sha512HashDigest
};
use Jabe\Impl\El\{
    CommandContextFunctions,
    DateTimeFunctions,
    ElProviderCompatibleInterface,
    ExpressionManagerInterface,
    JuelExpressionManager
};
//@TODO
use Jabe\Impl\ErrorCode\ExceptionCodeProvider;
use Jabe\Impl\Event\{
    CompensationEventHandler,
    ConditionalEventHandler,
    EventHandlerInterface,
    EventType,
    SignalEventHandler
};
use Jabe\Impl\ExteralTask\DefaultExternalTaskPriorityProvider;
use Jabe\Impl\Form\Deployer\FormDefinitionDeployer;
use Jabe\Impl\Form\Engine\{
    FormEngineInterface,
    HtmlFormEngine,
    JuelFormEngine
};
use Jabe\Impl\Form\Entity\FormDefinitionManager;
use Jabe\Impl\Form\Type\{
    AbstractFormFieldType,
    BooleanFormType,
    DateFormType,
    FormTypes,
    IntegerFormType,
    StringFormType
};
use Jabe\Impl\Form\Validator\{
    FormFieldValidatorInterface,
    FormValidators,
    MaxLengthValidator,
    MaxValidator,
    MinLengthValidator,
    MinValidator,
    ReadOnlyValidator,
    RequiredValidator
};
use Jabe\Impl\History\{
    DefaultHistoryRemovalTimeProvider,
    HistoryLevel,
    HistoryRemovalTimeProviderInterface
};
use Jabe\Impl\History\Event\{
    HostnameProviderInterface,
    SimpleIpBasedProvider
};
use Jabe\Impl\History\Handler\{
    CompositeDbHistoryEventHandler,
    CompositeHistoryEventHandler,
    DbHistoryEventHandler,
    HistoryEventHandlerInterface
};
use Jabe\Impl\History\Parser\{
    HistoryParseListener
};
use Jabe\Impl\History\Producer\{
    CacheAwareHistoryEventProducer,
    DefaultHistoryEventProducer,
    HistoryEventProducerInterface
};
use Jabe\Impl\Identity\{
    DefaultPasswordPolicyImpl,
    ReadOnlyIdentityProviderInterface,
    WritableIdentityProviderInterface
};
use Jabe\Impl\Identity\Db\DbIdentityServiceProvider;
use Jabe\Impl\Incident\{
    CompositeIncidentHandler,
    DefaultIncidentHandler,
    IncidentHandlerInterface
};
use Jabe\Impl\Interceptor\{
    CommandContextFactory,
    CommandExecutorInterface,
    CommandExecutorImpl,
    CommandInterceptor,
    DelegateInterceptorInterface,
    //@TODO
    ExceptionCodeInterceptor,
    SessionFactoryInterface
};
use Jabe\Impl\JobExecutor\{
    AsyncContinuationJobHandler,
    DefaultFailedJobCommandFactory,
    DefaultJobExecutor,
    DefaultJobPriorityProvider,
    FailedJobCommandFactoryInterface,
    JobDeclaration,
    JobExecutor,
    JobHandlerInterface,
    NotifyAcquisitionRejectedJobsHandler,
    ProcessEventJobHandler,
    RejectedJobsHandlerInterface,
    TimerActivateJobDefinitionHandler,
    TimerActivateProcessDefinitionHandler,
    TimerCatchIntermediateEventJobHandler,
    TimerExecuteNestedActivityJobHandler,
    TimerStartEventJobHandler,
    TimerSuspendJobDefinitionHandler,
    TimerSuspendProcessDefinitionHandler,
    TimerTaskListenerJobHandler
};
use Jabe\Impl\Metrics\{
    MetricsRegistry,
    MetricsReporterIdProviderInterface
};
use Jabe\Impl\Metrics\Parser\MetricsBpmnParseListener;
use Jabe\Impl\Metrics\Reporter\DbMetricsReporter;
//@TODO
//use Jabe\Impl\Migration\{};
//@TODO
use Jabe\Impl\Optimize\OptimizeManager;
use Jabe\Impl\Persistence\GenericManagerFactory;
use Jabe\Impl\Persistence\Deploy\DeployerInterface;
use Jabe\Impl\Persistence\Deploy\Cache\{
    CacheFactoryInterface,
    DefaultCacheFactory,
    DeploymentCache
};
use Jabe\Impl\Persistence\Entity\{
    AttachmentManager,
    AuthorizationManager,
    BatchManager,
    ByteArrayManager,
    CommentManager,
    DeploymentManager,
    EventSubscriptionManager,
    ExecutionManager,
    ExternalTaskManager,
    FilterManager,
    HistoricActivityInstanceManager,
    HistoricBatchManager,
    HistoricDetailManager,
    HistoricExternalTaskLogManager,
    HistoricIdentityLinkLogManager,
    HistoricIncidentManager,
    HistoricJobLogManager,
    HistoricProcessInstanceManager,
    HistoricStatisticsManager,
    HistoricTaskInstanceManager,
    HistoricVariableInstanceManager,
    IdentityInfoManager,
    IdentityLinkManager,
    IncidentManager,
    JobDefinitionManager,
    JobManager,
    MeterLogManager,
    ProcessDefinitionManager,
    PropertyManager,
    ReportManager,
    ResourceManager,
    SchemaLogManager,
    StatisticsManager,
    TableDataManager,
    TaskManager,
    TaskReportManager,
    TenantManager,
    UserOperationLogManager,
    VariableInstanceManager
};
use Jabe\Impl\Repository\DefaultDeploymentHandlerFactory;
use Jabe\Impl\Runtime\{
    ConditionHandlerInterface,
    CorrelationHandlerInterface,
    DefaultConditionHandler,
    //@TODO
    DefaultCorrelationHandler,
    DefaultDeserializationTypeValidator
};
use Jabe\Impl\Scripting\ScriptFactory;
use Jabe\Impl\Scripting\Engine\{
    BeansResolverFactory,
    DefaultScriptEngineResolver,
    ResolverFactoryInterface,
    ScriptBindingsFactory,
    ScriptingEngines,
    VariableScopeResolverFactory
};
use Jabe\Impl\Scripting\Env\{
    ScriptEnvResolverInterface,
    ScriptingEnvironment
};
use Jabe\Impl\Telemetry\TelemetryRegistry;
use Jabe\Impl\Telemetry\Dto\{
    DatabaseImpl,
    InternalsImpl,
    //@TODO
    DbalImpl,
    ProductImpl,
    TelemetryDataImpl
};
use Jabe\Impl\Telemetry\Reporter\TelemetryReporter;
use Jabe\Impl\Util\{
    ClockUtil,
    IoUtil,
    ParseUtil,
    ProcessEngineDetails,
    ReflectUtil,
    EnsureUtil
};
use Jabe\Impl\Variable\ValueTypeResolverImpl;
use Jabe\Impl\Variable\Serializer\{
    BooleanValueSerializer,
    //@TODO
    //ByteArrayValueSerializer
    DateValueSerializer,
    DefaultVariableSerializers,
    DoubleValueSerializer,
    FileValueSerializer,
    //@TODO
    IntegerValueSerializer,
    PhpObjectSerializer,
    NullValueSerializer,
    StringValueSerializer,
    TypedValueSerializerInterface,
    VariableSerializerFactoryInterface,
    VariableSerializersInterface
};
use Jabe\Management\Metrics;
use Jabe\Repository\{
    DeploymentBuilderInterface,
    DeploymentHandlerFactoryInterface
};
use Jabe\Runtime\{
    IncidentInterface,
    WhitelistingDeserializationTypeValidatorInterface
};
use Jabe\Task\TaskQueryInterface;
use Jabe\Variable\Variables;
use Jabe\Variable\Type\ValueTypeInterface;
use MyBatis\Builder\Xml\XMLConfigBuilder;
use MyBatis\DataSource\DataSourceInterface;
use MyBatis\DataSource\Unpooled\UnpooledDataSource;
use MyBatis\Mapping\Environment;
use MyBatis\Session\{
    Configuration,
    ExecutorType,
    SqlSessionFactoryInterface
};
use MyBatis\Session\Defaults\DefaultSqlSessionFactory;
use MyBatis\Transaction\TransactionFactoryInterface;
use MyBatis\Transaction\Dbal\DbalTransactionFactory;
use MyBatis\Transaction\Managed\ManagedTransactionFactory;
use Script\{
    ScriptEngineManager,
    ScriptEngineResolverInterface
};

abstract class ProcessEngineConfigurationImpl extends ProcessEngineConfiguration
{

}
