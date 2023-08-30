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
    DefaultArtifactFactory,
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
use Jabe\Impl\Event\EventHandlerImpl;
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
use Jabe\Impl\ExternalTask\DefaultExternalTaskPriorityProvider;
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
    HistoryLevelInterface,
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
    TimerStartEventSubprocessJobHandler,
    TimerSuspendJobDefinitionHandler,
    TimerSuspendProcessDefinitionHandler,
    TimerTaskListenerJobHandler
};
//@TODO
//use Jabe\Impl\JobExecutor\HistoryCleanup
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
use Jabe\Test\Mock\MocksResolverFactory;
use Jabe\Variable\Variables;
use Jabe\Variable\Type\ValueTypeInterface;
use MyBatis\Builder\Xml\XMLConfigBuilder as MyBatisXMLConfigBuilder;
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
    //protected final static ConfigurationLogger LOG = ConfigurationLogger.CONFIG_LOGGER;

    public const DB_SCHEMA_UPDATE_CREATE = "create";
    public const DB_SCHEMA_UPDATE_DROP_CREATE = "drop-create";

    public const HISTORYLEVEL_NONE = 0; // = HistoryLevel.HISTORY_LEVEL_NONE->getId();
    public const HISTORYLEVEL_ACTIVITY = 1; // = HistoryLevel.HISTORY_LEVEL_ACTIVITY->getId();
    public const HISTORYLEVEL_AUDIT = 2; // = HistoryLevel.HISTORY_LEVEL_AUDIT->getId();
    public const HISTORYLEVEL_FULL = 3; // = HistoryLevel.HISTORY_LEVEL_FULL->getId();

    public const DEFAULT_WS_SYNC_FACTORY = "CxfWebServiceClientFactory";

    public const DEFAULT_MYBATIS_MAPPING_FILE = "Resources/Impl/Mapping/Mappings.xml";

    public const DEFAULT_FAILED_JOB_LISTENER_MAX_RETRIES = 3;

    public const DEFAULT_INVOCATIONS_PER_BATCH_JOB = 1;

    protected static $defaultBeansMap = [];

    protected const PRODUCT_NAME = "BPM Runtime";

    public static $cachedSqlSessionFactory;

    // SERVICES /////////////////////////////////////////////////////////////////

    protected $repositoryService;// = new RepositoryServiceImpl();
    protected $runtimeService;// = new RuntimeServiceImpl();
    protected $historyService;// = new HistoryServiceImpl();
    protected $identityService;// = new IdentityServiceImpl();
    protected $taskService;// = new TaskServiceImpl();
    protected $formService;// = new FormServiceImpl();
    protected $managementService;// = new ManagementServiceImpl(this);
    protected $authorizationService;// = new AuthorizationServiceImpl();
    //protected CaseService caseService = new CaseServiceImpl();
    protected $filterService;// = new FilterServiceImpl();
    protected $externalTaskService;// = new ExternalTaskServiceImpl();
    //protected DecisionService decisionService = new DecisionServiceImpl();
    protected $optimizeService;// = new OptimizeService();

    // COMMAND EXECUTORS ////////////////////////////////////////////////////////

    // Command executor and interceptor stack
    /**
     * the configurable list which will be {@link #initInterceptorChain(Php.util.List) processed} to build the {@link #commandExecutorTxRequired}
     */
    protected $customPreCommandInterceptorsTxRequired = [];
    protected $customPostCommandInterceptorsTxRequired = [];
    protected $commandInterceptorsTxRequired = [];

    /**
     * this will be initialized during the configurationComplete()
     */
    protected $commandExecutorTxRequired;

    /**
     * the configurable list which will be {@link #initInterceptorChain(List) processed} to build the {@link #commandExecutorTxRequiresNew}
     */
    protected $customPreCommandInterceptorsTxRequiresNew = [];
    protected $customPostCommandInterceptorsTxRequiresNew = [];

    protected $commandInterceptorsTxRequiresNew = [];

    /**
     * this will be initialized during the configurationComplete()
     */
    protected $commandExecutorTxRequiresNew;

    /**
     * Separate command executor to be used for db schema operations. Must always use NON-JTA transactions
     */
    protected $commandExecutorSchemaOperations;

    /**
     * Allows for specific commands to be retried when using CockroachDB. This is due to the fact that
     * OptimisticLockingExceptions can't be handled on CockroachDB and transactions must be rolled back.
     * The commands where CockroachDB retries are possible are:
     *
     * <ul>
     *   <li>BootstrapEngineCommand</li>
     *   <li>AcquireJobsCmd</li>
     *   <li>DeployCmd</li>
     *   <li>FetchExternalTasksCmd</li>
     *   <li>HistoryCleanupCmd</li>
     *   <li>HistoryLevelSetupCommand</li>
     * </ul>
     */
    protected int $commandRetries = 0;

    // SESSION FACTORIES ////////////////////////////////////////////////////////

    protected $customSessionFactories = [];
    protected $dbSqlSessionFactory;
    protected $sessionFactories = [];

    // DEPLOYERS ////////////////////////////////////////////////////////////////

    protected $customPreDeployers = [];
    protected $customPostDeployers = [];
    protected $deployers = [];
    protected $deploymentCache;

    // CACHE ////////////////////////////////////////////////////////////////////

    protected $cacheFactory;
    protected int $cacheCapacity = 1000;
    protected bool $enableFetchProcessDefinitionDescription = true;

    // JOB EXECUTOR /////////////////////////////////////////////////////////////

    protected $customJobHandlers = [];
    protected $jobHandlers = [];
    protected $jobExecutor;

    protected $jobPriorityProvider;

    protected int $jobExecutorPriorityRangeMin = 0;
    protected int $jobExecutorPriorityRangeMax = PHP_INT_MAX;

    // EXTERNAL TASK /////////////////////////////////////////////////////////////
    protected $externalTaskPriorityProvider;

    // MYBATIS SQL SESSION FACTORY //////////////////////////////////////////////

    protected $sqlSessionFactory;
    protected $transactionFactory;

    // ID GENERATOR /////////////////////////////////////////////////////////////
    protected $idGenerator;
    protected $idGeneratorDataSource;
    //protected $idGeneratorDataSourceJndiName;

    // INCIDENT HANDLER /////////////////////////////////////////////////////////

    protected $incidentHandlers = [];
    protected $customIncidentHandlers = [];

    // BATCH ////////////////////////////////////////////////////////////////////

    protected $batchHandlers = [];
    protected $customBatchJobHandlers = [];

    /**
     * Number of jobs created by a batch seed job invocation
     */
    protected int $batchJobsPerSeed = 100;
    /**
     * Number of invocations executed by a single batch job
     */
    protected $invocationsPerBatchJob = self::DEFAULT_INVOCATIONS_PER_BATCH_JOB;

    /**
     * Map to set an individual value for each batch type to
     * control the invocations per batch job. Unless specified
     * in this map, value of 'invocationsPerBatchJob' is used.
     */
    protected $invocationsPerBatchJobByBatchType = [];

    /**
     * seconds to wait between polling for batch completion
     */
    protected int $batchPollTime = 30;
    /**
     * default priority for batch jobs
     */
    protected $batchJobPriority = DefaultJobPriorityProvider::DEFAULT_PRIORITY;

    // OTHER ////////////////////////////////////////////////////////////////////
    protected $customFormEngines = [];
    protected $formEngines = [];

    protected $customFormTypes = [];
    protected $formTypes;
    protected $formValidators;
    protected $customFormFieldValidators = [];

    /** don't throw parsing exceptions for Forms if set to true*/
    protected bool $disableStrictFormParsing = false;

    protected $customPreVariableSerializers = [];
    protected $customPostVariableSerializers = [];
    protected $variableSerializers;
    protected $fallbackSerializerFactory;

    protected $defaultSerializationFormat = 'PHP';
    protected bool $phpSerializationFormatEnabled = false;
    protected $defaultCharsetName = null;
    protected $defaultCharset = null;

    protected $expressionManager;
    //protected ElProvider dmnElProvider;
    protected $scriptingEngines;
    protected $resolverFactories = [];
    protected $scriptingEnvironment;
    protected $scriptEnvResolvers = [];
    protected $scriptFactory;
    protected $scriptEngineResolver;
    protected $scriptEngineNameJavaScript;
    protected bool $autoStoreScriptVariables = false;
    protected bool $enableScriptCompilation = true;
    protected bool $enableScriptEngineCaching = true;
    protected bool $enableFetchScriptEngineFromProcessApplication = true;
    protected bool $enableScriptEngineLoadExternalResources = false;
    protected bool $enableScriptEngineNashornCompatibility = false;
    protected bool $configureScriptEngineHostAccess = true;

    /**
     * When set to false, the following behavior changes:
     * <ul>
     * <li>The automated schema maintenance (creating $and dropping tables, see property <code>databaseSchemaUpdate</code>)
     *   does not cover the tables required for CMMN execution.</li>
     * <li>CMMN resources are not deployed as {@link CaseDefinition} to the engine.</li>
     * <li>Tasks from CMMN cases are not returned by the {@link TaskQuery}.</li>
     * </ul>
     */
    protected bool $cmmnEnabled = false;

      /**
     * When set to false, the following behavior changes:
     * <ul>
     * <li>The automated schema maintenance (creating $and dropping tables, see property <code>databaseSchemaUpdate</code>)
     *   does not cover the tables required for DMN execution.</li>
     * <li>DMN resources are not deployed as {@link DecisionDefinition} or
     *   {@link DecisionRequirementsDefinition} to the engine.</li>
     * </ul>
     */
    protected bool $dmnEnabled = false;
    /**
     * When set to <code>false</code>, the following behavior changes:
     * <ul>
     *   <li>Standalone tasks can no longer be created via API.</li>
     *   <li>Standalone tasks are not returned by the TaskQuery.</li>
     * </ul>
     */
    protected bool $standaloneTasksEnabled = true;

    protected bool $enableGracefulDegradationOnContextSwitchFailure = true;

    protected $businessCalendarManager;

    protected $wsSyncFactoryClassName = self::DEFAULT_WS_SYNC_FACTORY;

    protected $commandContextFactory;
    protected $transactionContextFactory;
    protected $bpmnParseFactory;

    // cmmn
    //protected CmmnTransformFactory cmmnTransformFactory;
    //protected DefaultCmmnElementHandlerRegistry cmmnElementHandlerRegistry;

    // dmn
    //protected DefaultDmnEngineConfiguration dmnEngineConfiguration;
    //protected DmnEngine dmnEngine;

    /**
     * a list of DMN FEEL custom function providers
     */
    //protected List<FeelCustomFunctionProvider> dmnFeelCustomFunctionProviders;

    /**
     * Enable DMN FEEL legacy behavior
     */
    //protected boolean dmnFeelEnableLegacyBehavior = false;

    protected $historyLevel;

    /**
     * a list of supported history levels
     */
    protected $historyLevels = [];

    /**
     * a list of supported custom history levels
     */
    protected $customHistoryLevels = [];

    protected $preParseListeners = [];
    protected $postParseListeners = [];

    protected $customPreCmmnTransformListeners = [];
    protected $customPostCmmnTransformListeners = [];

    protected $beans = [];

    protected bool $isDbIdentityUsed = true;
    protected bool $isDbHistoryUsed = true;

    protected $delegateInterceptor;

    protected $actualCommandExecutor;

    protected $customRejectedJobsHandler;

    protected $eventHandlers = [];
    protected $customEventHandlers = [];

    protected $failedJobCommandFactory;

    protected $databaseTablePrefix = "";

    /**
     * In some situations you want to set the schema to use for table checks / generation if the database metadata
     * doesn't return that correctly, see https://jira.codehaus.org/browse/ACT-1220,
     * https://jira.codehaus.org/browse/ACT-1062
     */
    protected $databaseSchema = null;

    protected bool $isCreateDiagramOnDeploy = false;

    protected $processApplicationManager;

    protected $correlationHandler;

    protected $conditionHandler;

    /**
     * session factory to be used for obtaining identity provider sessions
     */
    protected $identityProviderSessionFactory;

    protected $passwordEncryptor;

    protected $customPasswordChecker;

    protected $passwordManager;

    protected $saltGenerator;

    protected $registeredDeployments = [];

    protected $deploymentHandlerFactory;

    protected $resourceAuthorizationProvider;

    protected $processEnginePlugins = [];

    protected $historyEventProducer;

    //protected CmmnHistoryEventProducer cmmnHistoryEventProducer;

    //protected DmnHistoryEventProducer dmnHistoryEventProducer;

    /**
     * As an instance of CompositeHistoryEventHandler
     * it contains all the provided history event handlers that process history events.
     */
    protected $historyEventHandler;

    /**
     *  Allows users to add additional {@link HistoryEventHandler}
     *  instances to process history events.
     */
    protected $customHistoryEventHandlers = [];

    /**
     * If true, the default {@link DbHistoryEventHandler} will be included in the list
     * of history event handlers.
     */
    protected bool $enableDefaultDbHistoryEventHandler = true;

    protected $permissionProvider;

    protected bool $isExecutionTreePrefetchEnabled = true;

    /**
     * If true, the incident handlers init as {@link CompositeIncidentHandler} and
     * multiple incident handlers can be added for the same Incident type.
     * However, only the result from the "main" incident handler will be returned.
     * <p>
     * All {@link customIncidentHandlers} will be added as sub handlers to {@link CompositeIncidentHandler} for same handler type.
     * <p>
     * By default, main handler is {@link DefaultIncidentHandler}.
     * To override the main handler you need create {@link CompositeIncidentHandler} with your main IncidentHandler and
     * init {@link incidentHandlers} before setting up the engine.
     *
     * @see CompositeIncidentHandler
     * @see #initIncidentHandlers
     */
    protected bool $isCompositeIncidentHandlersEnabled = false;

    /**
     * If true the process engine will attempt to acquire an exclusive lock before
     * creating a deployment.
     */
    protected bool $isDeploymentLockUsed = true;

    /**
     * If true then several deployments will be processed strictly sequentially. When false they may be processed in parallel.
     */
    protected bool $isDeploymentSynchronized = true;

    /**
     * Allows setting whether the process engine should try reusing the first level entity cache.
     * Default setting is false, enabling it improves performance of asynchronous continuations.
     */
    protected bool $isDbEntityCacheReuseEnabled = false;

    protected bool $isInvokeCustomVariableListeners = true;

    /**
     * The process engine created by this configuration.
     */
    protected $processEngine;

    /**
     * used to create instances for listeners, JavaDelegates, etc
     */
    protected $artifactFactory;

    protected $dbEntityCacheKeyMapping;// = DbEntityCacheKeyMapping.defaultEntityCacheKeyMapping();

    /**
     * the metrics registry
     */
    protected $metricsRegistry;

    protected $dbMetricsReporter;

    protected bool $isMetricsEnabled = true;
    protected bool $isDbMetricsReporterActivate = false;

    protected $metricsReporterIdProvider;

    //disable telemetry
    protected bool $isTaskMetricsEnabled = false;

    /**
     * the historic job log host name
     */
    protected $hostname;
    protected $hostnameProvider;

    /**
     * handling of expressions submitted via API; can be used as guards against remote code execution
     */
    protected bool $enableExpressionsInAdhocQueries = false;
    protected bool $enableExpressionsInStoredQueries = true;

    /**
     * If false, disables XML eXternal Entity (XXE) Processing. This provides protection against XXE Processing attacks.
     */
    protected bool $enableXxeProcessing = false;

    /**
     * If true, user operation log entries are only written if there is an
     * authenticated user present in the context. If false, user operation log
     * entries are written regardless of authentication state.
     */
    protected bool $restrictUserOperationLogToAuthenticatedUsers = true;

    protected bool $disableStrictCallActivityValidation = false;

    protected bool $isBpmnStacktraceVerbose = false;

    protected bool $forceCloseMybatisConnectionPool = true;

    protected $tenantIdProvider = null;

    protected $commandCheckers = [];

    protected $adminGroups = [];

    protected $adminUsers = [];

    // Migration
    /*protected MigrationActivityMatcher migrationActivityMatcher;

    protected List<MigrationActivityValidator> customPreMigrationActivityValidators;
    protected List<MigrationActivityValidator> customPostMigrationActivityValidators;
    protected MigrationInstructionGenerator migrationInstructionGenerator;

    protected List<MigrationInstructionValidator> customPreMigrationInstructionValidators;
    protected List<MigrationInstructionValidator> customPostMigrationInstructionValidators;
    protected List<MigrationInstructionValidator> migrationInstructionValidators;

    protected List<MigratingActivityInstanceValidator> customPreMigratingActivityInstanceValidators;
    protected List<MigratingActivityInstanceValidator> customPostMigratingActivityInstanceValidators;
    protected List<MigratingActivityInstanceValidator> migratingActivityInstanceValidators;
    protected List<MigratingTransitionInstanceValidator> migratingTransitionInstanceValidators;
    protected List<MigratingCompensationInstanceValidator> migratingCompensationInstanceValidators;*/

    // Default user permission for task
    protected $defaultUserPermissionForTask;

    /**
     * Historic instance permissions are disabled by default
     */
    protected bool $enableHistoricInstancePermissions = false;

    protected bool $isUseSharedSqlSessionFactory = false;

    //History cleanup configuration
    protected $historyCleanupBatchWindowStartTime;
    protected $historyCleanupBatchWindowEndTime = "00:00";

    protected $historyCleanupBatchWindowStartTimeAsDate;
    protected $historyCleanupBatchWindowEndTimeAsDate;

    protected $historyCleanupBatchWindows = [];

    //shortcuts for batch windows configuration available to be configured from XML
    protected $mondayHistoryCleanupBatchWindowStartTime;
    protected $mondayHistoryCleanupBatchWindowEndTime;
    protected $tuesdayHistoryCleanupBatchWindowStartTime;
    protected $tuesdayHistoryCleanupBatchWindowEndTime;
    protected $wednesdayHistoryCleanupBatchWindowStartTime;
    protected $wednesdayHistoryCleanupBatchWindowEndTime;
    protected $thursdayHistoryCleanupBatchWindowStartTime;
    protected $thursdayHistoryCleanupBatchWindowEndTime;
    protected $fridayHistoryCleanupBatchWindowStartTime;
    protected $fridayHistoryCleanupBatchWindowEndTime;
    protected $saturdayHistoryCleanupBatchWindowStartTime;
    protected $saturdayHistoryCleanupBatchWindowEndTime;
    protected $sundayHistoryCleanupBatchWindowStartTime;
    protected $sundayHistoryCleanupBatchWindowEndTime;

    protected int $historyCleanupDegreeOfParallelism = 1;

    protected $historyTimeToLive;

    protected $batchOperationHistoryTimeToLive;
    protected $batchOperationsForHistoryCleanup = [];
    protected $parsedBatchOperationsForHistoryCleanup = [];

    /**
     * Default priority for history cleanup jobs. */
    protected $historyCleanupJobPriority = DefaultJobPriorityProvider::DEFAULT_PRIORITY;

    /**
     * Time to live for historic job log entries written by history cleanup jobs.
     * Must be an ISO-8601 conform String specifying only a number of days. Only
     * works in conjunction with removal-time-based cleanup strategy.
     */
    protected $historyCleanupJobLogTimeToLive;

    protected $taskMetricsTimeToLive;
    protected $parsedTaskMetricsTimeToLive;

    protected $batchWindowManager;// = new DefaultBatchWindowManager();

    protected $historyRemovalTimeProvider;

    protected $historyRemovalTimeStrategy;

    protected $historyCleanupStrategy;
    /**
     * Size of batch in which history cleanup data will be deleted. {@link HistoryCleanupBatch#MAX_BATCH_SIZE} must be respected.
     */
    private int $historyCleanupBatchSize = 500;
    /**
     * Indicates the minimal amount of data to trigger the history cleanup.
     */
    private int $historyCleanupBatchThreshold = 10;

    private bool $historyCleanupMetricsEnabled = true;

    /**
     * Controls whether engine participates in history cleanup or not.
     */
    protected bool $historyCleanupEnabled = true;

    private $failedJobListenerMaxRetries = self::DEFAULT_FAILED_JOB_LISTENER_MAX_RETRIES;

    protected $failedJobRetryTimeCycle;

    // login attempts ///////////////////////////////////////////////////////
    protected int $loginMaxAttempts = 10;
    protected int $loginDelayFactor = 2;
    protected int $loginDelayMaxTime = 60;
    protected int $loginDelayBase = 3;

    // max results limit
    protected int $queryMaxResultsLimit = PHP_INT_MAX;

    // logging context property names (with $default values)
    protected $loggingContextActivityId = "activityId";
    protected $loggingContextActivityName = "activityName";
    protected $loggingContextApplicationName = "applicationName";
    protected $loggingContextBusinessKey;// default === null => disabled by default
    protected $loggingContextProcessDefinitionId = "processDefinitionId";
    protected $loggingContextProcessDefinitionKey;// default === null => disabled by default
    protected $loggingContextProcessInstanceId = "processInstanceId";
    protected $loggingContextTenantId = "tenantId";
    protected $loggingContextEngineName = "engineName";

    // logging levels (with $default values)
    protected $logLevelBpmnStackTrace = "DEBUG";

    // telemetry ///////////////////////////////////////////////////////
    /**
     * Sets the initial property value of telemetry configuration only once
     * when it has never been enabled/disabled before.
     * Subsequent changes can be done only via the
     * {@link ManagementService#toggleTelemetry(boolean) Telemetry} API in {@link ManagementService}
     */
    protected $initializeTelemetry = null;
    /** The endpoint which telemetry is sent to */
    protected $telemetryEndpoint = "http://localhost:8081/pings";
    /** The number of times the telemetry request is retried in case it fails **/
    protected int $telemetryRequestRetries = 2;
    protected $telemetryReporter;
    /** Determines if the telemetry reporter thread runs. For telemetry to be sent,
     * this flag must be set to <code>true</code> and telemetry must be enabled via API
     * (see {@link ManagementService#toggleTelemetry(boolean)}. */
    protected bool $isTelemetryReporterActivate = false;
    /** http client used for sending telemetry */
    protected $telemetryHttpConnector;
    /** default: once every 24 hours */
    protected int $telemetryReportingPeriod = 24 * 60 * 60;
    protected $telemetryData;
    /** the connection and socket timeout configuration of the telemetry request
     * in milliseconds
     *  default: 15 seconds */
    protected int $telemetryRequestTimeout = 15 * 1000;
    // Exception Codes ///////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Disables the {@link ExceptionCodeInterceptor} and therefore the whole exception code feature.
     */
    protected bool $disableExceptionCode = false;

    /**
     * Disables the default implementation of {@link ExceptionCodeProvider} which allows overriding the reserved
     * exception codes > {@link ExceptionCodeInterceptor#MAX_CUSTOM_CODE} or < {@link ExceptionCodeInterceptor#MIN_CUSTOM_CODE}.
     */
    //@TODO enable, implement ExceptionCodeProvider
    protected bool $disableBuiltinExceptionCodeProvider = false;

    /**
     * Allows registering a custom implementation of a {@link ExceptionCodeProvider}
     * allowing to provide custom exception codes.
     */
    protected $customExceptionCodeProvider;

    /**
     * Holds the default implementation of {@link ExceptionCodeProvider}.
     */
    protected $builtinExceptionCodeProvider;

    private bool $preventJobExecutorInitialization = false;

    //JobExecutor state for synchronization
    protected $isActive;
    protected $isJobAdded;
    protected $processLock;

    //ClockUtil state synchronization
    protected $isClockReset;
    protected $currentTimestamp;

    public function __construct()
    {
        parent::__construct();

        $this->setJobExecutorState(
            new \Swoole\Atomic(0), //isActive
            new \Swoole\Atomic(0), //isJobAdded
            new \Swoole\Lock(SWOOLE_MUTEX), //database simple mutex,
            new \Swoole\Atomic(0), //isClockReset
            new \Swoole\Atomic\Long(0) //Current time timestamp in seconds
        );

        $this->repositoryService = new RepositoryServiceImpl();
        $this->runtimeService = new RuntimeServiceImpl();
        $this->historyService = new HistoryServiceImpl();
        $this->identityService = new IdentityServiceImpl();
        $this->taskService = new TaskServiceImpl();
        $this->formService = new FormServiceImpl();
        $this->managementService = new ManagementServiceImpl($this);
        $this->authorizationService = new AuthorizationServiceImpl();
        //protected CaseService caseService = new CaseServiceImpl();
        $this->filterService = new FilterServiceImpl();
        $this->externalTaskService = new ExternalTaskServiceImpl();
        //protected DecisionService decisionService = new DecisionServiceImpl();
        $this->optimizeService = new OptimizeService();
        $this->dbEntityCacheKeyMapping = DbEntityCacheKeyMapping::defaultEntityCacheKeyMapping();
    }

    public function getJobExecutorState(): array
    {
        return [$this->isActive, $this->isJobAdded, $this->processLock, $this->isClockReset, $this->currentTimestamp];
    }

    public function setJobExecutorState(...$args): void
    {
        $this->isActive = $args[0];
        $this->isJobAdded = $args[1];
        $this->processLock = $args[2];
        $this->isClockReset = $args[3];
        $this->currentTimestamp = $args[4];
    }

    /**
     * @return {@code true} if the exception code feature is disabled and vice-versa.
     */
    public function isDisableExceptionCode(): bool
    {
        return $this->disableExceptionCode;
    }

    /**
     * Setter to disables the {@link ExceptionCodeInterceptor} and therefore the whole exception code feature.
     */
    public function setDisableExceptionCode(bool $disableExceptionCode): void
    {
        $this->disableExceptionCode = $disableExceptionCode;
    }

    /**
     * @return {@code true} if the built-in exception code provider is disabled and vice-versa.
     */
    public function isDisableBuiltinExceptionCodeProvider(): bool
    {
        return $this->disableBuiltinExceptionCodeProvider;
    }

    /**
     * Setter to disables the default implementation of {@link ExceptionCodeProvider} which allows overriding the reserved
     * exception codes > {@link ExceptionCodeInterceptor#MAX_CUSTOM_CODE} or < {@link ExceptionCodeInterceptor#MIN_CUSTOM_CODE}.
     */
    public function setDisableBuiltinExceptionCodeProvider(bool $disableBuiltinExceptionCodeProvider): void
    {
        $this->disableBuiltinExceptionCodeProvider = $disableBuiltinExceptionCodeProvider;
    }

    /**
     * @return a custom implementation of a {@link ExceptionCodeProvider} allowing to provide custom error codes.
     */
    public function getCustomExceptionCodeProvider(): ExceptionCodeProvider
    {
        return $this->customExceptionCodeProvider;
    }

    /**
     * Setter to register a custom implementation of a {@link ExceptionCodeProvider} allowing to provide custom error codes.
     */
    public function setCustomExceptionCodeProvider(ExceptionCodeProvider $customExceptionCodeProvider): void
    {
        $this->customExceptionCodeProvider = $customExceptionCodeProvider;
    }

    public function getBuiltinExceptionCodeProvider(): ExceptionCodeProvider
    {
        return $this->builtinExceptionCodeProvider;
    }

    public function setBuiltinExceptionCodeProvider(ExceptionCodeProvider $builtinExceptionCodeProvider): void
    {
        $this->builtinExceptionCodeProvider = $builtinExceptionCodeProvider;
    }

    // buildProcessEngine ///////////////////////////////////////////////////////

    public function buildProcessEngine(bool $preventJobExecutorInitialization = false): ProcessEngineInterface
    {
        $this->preventJobExecutorInitialization = $preventJobExecutorInitialization;
        //Process must be reset in case of context switch
        $this->init();
        $this->processEngine = new ProcessEngineImpl($this);
        $this->invokePostProcessEngineBuild($this->processEngine);
        return $this->processEngine;
    }

    // init /////////////////////////////////////////////////////////////////////

    protected function init(): void
    {
        $this->invokePreInit();
        $this->initDefaultCharset();
        $this->initHistoryLevel();
        $this->initHistoryEventProducer();
        //initCmmnHistoryEventProducer();
        //initDmnHistoryEventProducer();
        $this->initHistoryEventHandler();
        $this->initExpressionManager();
        $this->initBeans();
        $this->initArtifactFactory();
        $this->initFormEngines();
        $this->initFormTypes();
        $this->initFormFieldValidators();
        $this->initScripting();
        //initDmnEngine();
        $this->initBusinessCalendarManager();
        $this->initCommandContextFactory();
        $this->initTransactionContextFactory();

        // Database type needs to be detected before CommandExecutors are initialized
        $this->initDataSource();
        $this->initExceptionCodeProvider();
        $this->initCommandExecutors();
        $this->initServices();
        $this->initIdGenerator();
        $this->initFailedJobCommandFactory();
        $this->initDeployers();
        $this->initJobProvider();
        $this->initExternalTaskPriorityProvider();
        $this->initBatchHandlers();
        $this->initJobExecutor();
        $this->initTransactionFactory();
        $this->initSqlSessionFactory();
        $this->initIdentityProviderSessionFactory();
        $this->initSessionFactories();
        $this->initValueTypeResolver();
        $this->initTypeValidator();
        $this->initSerialization();
        //initJpa();
        $this->initDelegateInterceptor();
        $this->initEventHandlers();
        $this->initProcessApplicationManager();
        $this->initCorrelationHandler();
        $this->initConditionHandler();
        $this->initIncidentHandlers();

        //@TODO
        //$this->initPasswordDigest();
        $this->initDeploymentRegistration();
        $this->initDeploymentHandlerFactory();
        //@TODO
        //$this->initResourceAuthorizationProvider();
        //@TODO
        //$this->initPermissionProvider();
        $this->initHostName();
        $this->initMetrics();
        //$this->initTelemetry();
        //initMigration();
        $this->initCommandCheckers();
        $this->initDefaultUserPermissionForTask();
        $this->initHistoryRemovalTime();
        //$this->initHistoryCleanup();
        $this->initInvocationsPerBatchJobByBatchType();
        $this->initAdminUser();
        $this->initAdminGroups();
        $this->initPasswordPolicy();
        $this->invokePostInit();
    }

    public function initExceptionCodeProvider(): void
    {
        if (!$this->isDisableBuiltinExceptionCodeProvider()) {
            $this->builtinExceptionCodeProvider = new class () extends ExceptionCodeProvider {
            };
        }
    }

    protected function initTypeValidator(): void
    {
        if ($this->deserializationTypeValidator === null) {
            $this->deserializationTypeValidator = new DefaultDeserializationTypeValidator();
        }
        if ($this->deserializationTypeValidator instanceof WhitelistingDeserializationTypeValidatorInterface) {
            $validator = $this->deserializationTypeValidator;
            $validator->setAllowedClasses($this->deserializationAllowedClasses);
            $validator->setAllowedPackages($this->deserializationAllowedPackages);
        }
    }

    public function initHistoryRemovalTime(): void
    {
        $this->initHistoryRemovalTimeProvider();
        $this->initHistoryRemovalTimeStrategy();
    }

    public function initHistoryRemovalTimeStrategy(): void
    {
        if ($this->historyRemovalTimeStrategy === null) {
            $this->historyRemovalTimeStrategy = self::HISTORY_REMOVAL_TIME_STRATEGY_END;
        }

        if (
            self::HISTORY_REMOVAL_TIME_STRATEGY_START != $this->historyRemovalTimeStrategy &&
            self::HISTORY_REMOVAL_TIME_STRATEGY_END != $this->historyRemovalTimeStrategy &&
            self::HISTORY_REMOVAL_TIME_STRATEGY_NONE != $this->historyRemovalTimeStrategy
        ) {
            throw new \Exception(sprintf("history removal time strategy must be set to '%s', '%s' or '%s'", self::HISTORY_REMOVAL_TIME_STRATEGY_START, self::HISTORY_REMOVAL_TIME_STRATEGY_END, self::HISTORY_REMOVAL_TIME_STRATEGY_NONE));
            //throw LOG.invalidPropertyValue("historyRemovalTimeStrategy", String.valueOf(historyRemovalTimeStrategy),
            //String.format("history removal time strategy must be set to '%s', '%s' or '%s'", HISTORY_REMOVAL_TIME_STRATEGY_START, HISTORY_REMOVAL_TIME_STRATEGY_END, HISTORY_REMOVAL_TIME_STRATEGY_NONE));
        }
    }

    public function initHistoryRemovalTimeProvider(): void
    {
        if ($this->historyRemovalTimeProvider === null) {
            $this->historyRemovalTimeProvider = new DefaultHistoryRemovalTimeProvider();
        }
    }

    /*public function initHistoryCleanup(): void
    {
        $this->initHistoryCleanupStrategy();

        //validate number of threads
        if ($this->historyCleanupDegreeOfParallelism < 1 || $this->historyCleanupDegreeOfParallelism > HistoryCleanupCmd::MAX_THREADS_NUMBER) {
            throw new \Exception(sprintf("value for number of threads for history cleanup should be between 1 and %s", HistoryCleanupCmd::MAX_THREADS_NUMBER));
            //throw LOG.invalidPropertyValue("historyCleanupDegreeOfParallelism", String.valueOf(historyCleanupDegreeOfParallelism),
            //String.format("value for number of threads for history cleanup should be between 1 and %s", HistoryCleanupCmd.MAX_THREADS_NUMBER));
        }

        if ($this->historyCleanupBatchWindowStartTime !== null) {
            $this->initHistoryCleanupBatchWindowStartTime();
        }

        if ($this->historyCleanupBatchWindowEndTime !== null) {
            $this->initHistoryCleanupBatchWindowEndTime();
        }

        $this->initHistoryCleanupBatchWindowsMap();

        if ($this->historyCleanupBatchSize > HistoryCleanupHandler::MAX_BATCH_SIZE || historyCleanupBatchSize <= 0) {
            throw LOG.invalidPropertyValue("historyCleanupBatchSize", String.valueOf(historyCleanupBatchSize),
                String.format("value for batch size should be between 1 and %s", HistoryCleanupHandler.MAX_BATCH_SIZE));
        }

        if (historyCleanupBatchThreshold < 0) {
            throw LOG.invalidPropertyValue("historyCleanupBatchThreshold", String.valueOf(historyCleanupBatchThreshold),
                "History cleanup batch threshold cannot be negative.");
        }

        $this->initHistoryTimeToLive();

        $this->initBatchOperationsHistoryTimeToLive();

        $this->initHistoryCleanupJobLogTimeToLive();

        $this->initTaskMetricsTimeToLive();
    }

    protected function initHistoryCleanupStrategy(): void
    {
        if (historyCleanupStrategy === null) {
            historyCleanupStrategy = HISTORY_CLEANUP_STRATEGY_REMOVAL_TIME_BASED;
        }

        if (!HISTORY_CLEANUP_STRATEGY_REMOVAL_TIME_BASED.equals(historyCleanupStrategy) &&
            !HISTORY_CLEANUP_STRATEGY_END_TIME_BASED.equals(historyCleanupStrategy)) {
            throw LOG.invalidPropertyValue("historyCleanupStrategy", String.valueOf(historyCleanupStrategy),
            String.format("history cleanup strategy must be either set to '%s' or '%s'", HISTORY_CLEANUP_STRATEGY_REMOVAL_TIME_BASED, HISTORY_CLEANUP_STRATEGY_END_TIME_BASED));
        }

        if (HISTORY_CLEANUP_STRATEGY_REMOVAL_TIME_BASED.equals(historyCleanupStrategy) &&
            HISTORY_REMOVAL_TIME_STRATEGY_NONE.equals(historyRemovalTimeStrategy)) {
            throw LOG.invalidPropertyValue("historyRemovalTimeStrategy", String.valueOf(historyRemovalTimeStrategy),
            String.format("history removal time strategy cannot be set to '%s' in conjunction with '%s' history cleanup strategy", HISTORY_REMOVAL_TIME_STRATEGY_NONE, HISTORY_CLEANUP_STRATEGY_REMOVAL_TIME_BASED));
        }
    }

    private void initHistoryCleanupBatchWindowsMap() {
        if (mondayHistoryCleanupBatchWindowStartTime !== null || mondayHistoryCleanupBatchWindowEndTime !== null) {
            historyCleanupBatchWindows.put(Calendar.MONDAY, new BatchWindowConfiguration(mondayHistoryCleanupBatchWindowStartTime, mondayHistoryCleanupBatchWindowEndTime));
        }

        if (tuesdayHistoryCleanupBatchWindowStartTime !== null || tuesdayHistoryCleanupBatchWindowEndTime !== null) {
            historyCleanupBatchWindows.put(Calendar.TUESDAY, new BatchWindowConfiguration(tuesdayHistoryCleanupBatchWindowStartTime, tuesdayHistoryCleanupBatchWindowEndTime));
        }

        if (wednesdayHistoryCleanupBatchWindowStartTime !== null || wednesdayHistoryCleanupBatchWindowEndTime !== null) {
            historyCleanupBatchWindows.put(Calendar.WEDNESDAY, new BatchWindowConfiguration(wednesdayHistoryCleanupBatchWindowStartTime, wednesdayHistoryCleanupBatchWindowEndTime));
        }

        if (thursdayHistoryCleanupBatchWindowStartTime !== null || thursdayHistoryCleanupBatchWindowEndTime !== null) {
            historyCleanupBatchWindows.put(Calendar.THURSDAY, new BatchWindowConfiguration(thursdayHistoryCleanupBatchWindowStartTime, thursdayHistoryCleanupBatchWindowEndTime));
        }

        if (fridayHistoryCleanupBatchWindowStartTime !== null || fridayHistoryCleanupBatchWindowEndTime !== null) {
            historyCleanupBatchWindows.put(Calendar.FRIDAY, new BatchWindowConfiguration(fridayHistoryCleanupBatchWindowStartTime, fridayHistoryCleanupBatchWindowEndTime));
        }

        if (saturdayHistoryCleanupBatchWindowStartTime !== null ||saturdayHistoryCleanupBatchWindowEndTime !== null) {
            historyCleanupBatchWindows.put(Calendar.SATURDAY, new BatchWindowConfiguration(saturdayHistoryCleanupBatchWindowStartTime, saturdayHistoryCleanupBatchWindowEndTime));
        }

        if (sundayHistoryCleanupBatchWindowStartTime !== null || sundayHistoryCleanupBatchWindowEndTime !== null) {
            historyCleanupBatchWindows.put(Calendar.SUNDAY, new BatchWindowConfiguration(sundayHistoryCleanupBatchWindowStartTime, sundayHistoryCleanupBatchWindowEndTime));
        }
    }*/

    protected function initInvocationsPerBatchJobByBatchType(): void
    {
        if ($this->invocationsPerBatchJobByBatchType === null) {
            $this->invocationsPerBatchJobByBatchType = [];
        } else {
            $batchTypes = array_keys($this->invocationsPerBatchJobByBatchType);
            foreach ($batchTypes as $key) {
                if (!array_key_exists($key, $this->batchHandlers)) {
                    //LOG::invalidBatchTypeForInvocationsPerBatchJob
                }
            }
        }
    }

    protected function initHistoryTimeToLive(): void
    {
        try {
            ParseUtil::parseHistoryTimeToLive($this->historyTimeToLive);
        } catch (\Exception $e) {
            //throw LOG.invalidPropertyValue("historyTimeToLive", historyTimeToLive, e);
            throw new \Exception("historyTimeToLive");
        }
    }

    protected function initBatchOperationsHistoryTimeToLive(): void
    {
        try {
            ParseUtil::parseHistoryTimeToLive($this->batchOperationHistoryTimeToLive);
        } catch (\Exception $e) {
            //throw LOG.invalidPropertyValue("batchOperationHistoryTimeToLive", batchOperationHistoryTimeToLive, e);
            throw new \Exception("batchOperationHistoryTimeToLive");
        }

        if ($this->batchOperationsForHistoryCleanup === null) {
            $this->batchOperationsForHistoryCleanup = [];
        } else {
            foreach (array_keys($this->batchOperationsForHistoryCleanup) as $batchOperation) {
                $timeToLive = $this->batchOperationsForHistoryCleanup[$batchOperation];
                if (!array_key_exists($batchOperation, $this->batchHandlers)) {
                    //LOG.invalidBatchOperation(batchOperation, timeToLive);
                }
                try {
                    ParseUtil::parseHistoryTimeToLive($timeToLive);
                } catch (\Exception $e) {
                    //throw LOG.invalidPropertyValue("history time to live for " + batchOperation + " batch operations", timeToLive, e);
                    throw new \Exception("history time to live ...");
                }
            }
        }

        if (empty($this->batchHandlers) && !empty($this->batchOperationHistoryTimeToLive)) {
            foreach (array_keys($this->batchHandlers) as $batchOperation) {
                if (!array_key_exists($batchOperation, $this->batchOperationsForHistoryCleanup)) {
                    $this->batchOperationsForHistoryCleanup[$batchOperation] = $this->batchOperationHistoryTimeToLive;
                }
            }
        }

        $this->parsedBatchOperationsForHistoryCleanup = [];
        if (!empty($this->batchOperationsForHistoryCleanup)) {
            foreach (array_keys($this->batchOperationsForHistoryCleanup) as $operation) {
                $historyTimeToLive = ParseUtil::parseHistoryTimeToLive($this->batchOperationsForHistoryCleanup[$operation]);
                $this->parsedBatchOperationsForHistoryCleanup[$operation] = $historyTimeToLive;
            }
        }
    }

    /*private function initHistoryCleanupBatchWindowEndTime(): void
    {
        try {
            $this->historyCleanupBatchWindowEndTimeAsDate = HistoryCleanupHelper.parseTimeConfiguration(historyCleanupBatchWindowEndTime);
        } catch (ParseException $e) {
            throw LOG.invalidPropertyValue("historyCleanupBatchWindowEndTime", historyCleanupBatchWindowEndTime);
        }
    }

    private void initHistoryCleanupBatchWindowStartTime() {
        try {
            historyCleanupBatchWindowStartTimeAsDate = HistoryCleanupHelper.parseTimeConfiguration(historyCleanupBatchWindowStartTime);
        } catch (ParseException $e) {
            throw LOG.invalidPropertyValue("historyCleanupBatchWindowStartTime", historyCleanupBatchWindowStartTime);
        }
    }

    protected function initHistoryCleanupJobLogTimeToLive(): void
    {
        try {
            ParseUtil.parseHistoryTimeToLive(historyCleanupJobLogTimeToLive);
        } catch (Exception $e) {
            throw LOG.invalidPropertyValue("historyCleanupJobLogTimeToLive", historyCleanupJobLogTimeToLive, e);
        }
    }

    protected function initTaskMetricsTimeToLive(): void
    {
        try {
            parsedTaskMetricsTimeToLive = ParseUtil.parseHistoryTimeToLive(taskMetricsTimeToLive);
        } catch (Exception $e) {
            throw LOG.invalidPropertyValue("taskMetricsTimeToLive", taskMetricsTimeToLive, e);
        }
    }*/

    protected function invokePreInit(): void
    {
        foreach ($this->processEnginePlugins as $plugin) {
            //LOG.pluginActivated(plugin.toString(), getProcessEngineName());
            $plugin->preInit($this);
        }
    }

    protected function invokePostInit(): void
    {
        foreach ($this->processEnginePlugins as $plugin) {
            $plugin->postInit($this);
        }
    }

    protected function invokePostProcessEngineBuild(ProcessEngineInterface $engine): void
    {
        foreach ($this->processEnginePlugins as $plugin) {
            $plugin->postProcessEngineBuild($engine);
        }
    }

    // failedJobCommandFactory ////////////////////////////////////////////////////////

    protected function initFailedJobCommandFactory(): void
    {
        if ($this->failedJobCommandFactory === null) {
            $this->failedJobCommandFactory = new DefaultFailedJobCommandFactory();
        }
        if ($this->postParseListeners === null) {
            $this->postParseListeners = [];
        }
        $this->postParseListeners[] = new DefaultFailedJobParseListener();
    }

    // incident handlers /////////////////////////////////////////////////////////////

    protected function initIncidentHandlers(): void
    {
        if (empty($this->incidentHandlers)) {
            $this->incidentHandlers = [];

            $failedJobIncidentHandler = new DefaultIncidentHandler(IncidentInterface::FAILED_JOB_HANDLER_TYPE);
            $failedExternalTaskIncidentHandler = new DefaultIncidentHandler(IncidentInterface::EXTERNAL_TASK_HANDLER_TYPE);

            if ($this->isCompositeIncidentHandlersEnabled) {
                $this->addIncidentHandler(new CompositeIncidentHandler($failedJobIncidentHandler));
                $this->addIncidentHandler(new CompositeIncidentHandler($failedExternalTaskIncidentHandler));
            } else {
                $this->addIncidentHandler($failedJobIncidentHandler);
                $this->addIncidentHandler($failedExternalTaskIncidentHandler);
            }
        }
        if (!empty($this->customIncidentHandlers)) {
            foreach ($this->customIncidentHandlers as $incidentHandler) {
                $this->addIncidentHandler($incidentHandler);
            }
        }
    }

    // batch ///////////////////////////////////////////////////////////////////////

    protected function initBatchHandlers(): void
    {
        if (empty($this->batchHandlers)) {
            $this->batchHandlers = [];

            //@TODO
            //$migrationHandler = new MigrationBatchJobHandler();
            //$this->batchHandlers[$migrationHandler->getType()] = $migrationHandler;

            $modificationHandler = new ModificationBatchJobHandler();
            $this->batchHandlers[$modificationHandler->getType()] = $modificationHandler;

            $deleteProcessJobHandler = new DeleteProcessInstancesJobHandler();
            $this->batchHandlers[$deleteProcessJobHandler->getType()] = $deleteProcessJobHandler;

            $deleteHistoricProcessInstancesJobHandler = new DeleteHistoricProcessInstancesJobHandler();
            $this->batchHandlers[$deleteHistoricProcessInstancesJobHandler->getType()] = $deleteHistoricProcessInstancesJobHandler;

            $setJobRetriesJobHandler = new SetJobRetriesJobHandler();
            $this->batchHandlers[$setJobRetriesJobHandler->getType()] = $setJobRetriesJobHandler;

            $setExternalTaskRetriesJobHandler = new SetExternalTaskRetriesJobHandler();
            $this->batchHandlers[$setExternalTaskRetriesJobHandler->getType()] = $setExternalTaskRetriesJobHandler;

            $restartProcessInstancesJobHandler = new RestartProcessInstancesJobHandler();
            $this->batchHandlers[$restartProcessInstancesJobHandler->getType()] = $restartProcessInstancesJobHandler;

            $suspendProcessInstancesJobHandler = new UpdateProcessInstancesSuspendStateJobHandler();
            $this->batchHandlers[$suspendProcessInstancesJobHandler->getType()] = $suspendProcessInstancesJobHandler;

            //$deleteHistoricDecisionInstancesJobHandler = new DeleteHistoricDecisionInstancesJobHandler();
            //$this->batchHandlers[$deleteHistoricDecisionInstancesJobHandler->getType()] = $deleteHistoricDecisionInstancesJobHandler;

            $processSetRemovalTimeJobHandler = new ProcessSetRemovalTimeJobHandler();
            $this->batchHandlers[$processSetRemovalTimeJobHandler->getType()] = $processSetRemovalTimeJobHandler;

            //$decisionSetRemovalTimeJobHandler = new DecisionSetRemovalTimeJobHandler();
            //batchHandlers.put(decisionSetRemovalTimeJobHandler->getType(), decisionSetRemovalTimeJobHandler);

            $batchSetRemovalTimeJobHandler = new BatchSetRemovalTimeJobHandler();
            $this->batchHandlers[$batchSetRemovalTimeJobHandler->getType()] = $batchSetRemovalTimeJobHandler;

            $batchSetVariablesHandler = new BatchSetVariablesHandler();
            $this->batchHandlers[$batchSetVariablesHandler->getType()] = $batchSetVariablesHandler;

            $messageCorrelationJobHandler = new MessageCorrelationBatchJobHandler();
            $this->batchHandlers[$messageCorrelationJobHandler->getType()] = $messageCorrelationJobHandler;
        }

        if (!empty($this->customBatchJobHandlers)) {
            foreach ($this->customBatchJobHandlers as $customBatchJobHandler) {
                $this->batchHandlers[$customBatchJobHandler->getType()] = $customBatchJobHandler;
            }
        }
    }

    // command executors ////////////////////////////////////////////////////////

    abstract protected function getDefaultCommandInterceptorsTxRequired(): array;

    abstract protected function getDefaultCommandInterceptorsTxRequiresNew(): array;

    protected function initCommandExecutors(): void
    {
        $this->initActualCommandExecutor();
        $this->initCommandInterceptorsTxRequired();
        $this->initCommandExecutorTxRequired();
        $this->initCommandInterceptorsTxRequiresNew();
        $this->initCommandExecutorTxRequiresNew();
        $this->initCommandExecutorDbSchemaOperations();
    }

    protected function initActualCommandExecutor(): void
    {
        $this->actualCommandExecutor = new CommandExecutorImpl();
    }

    protected function initCommandInterceptorsTxRequired(): void
    {
        if (empty($this->commandInterceptorsTxRequired)) {
            if (!empty($this->customPreCommandInterceptorsTxRequired)) {
                $this->commandInterceptorsTxRequired = $this->customPreCommandInterceptorsTxRequired;
            } else {
                $this->commandInterceptorsTxRequired = [];
            }
            $this->commandInterceptorsTxRequired = array_merge($this->commandInterceptorsTxRequired, $this->getDefaultCommandInterceptorsTxRequired());
            if (!empty($this->customPostCommandInterceptorsTxRequired)) {
                $this->commandInterceptorsTxRequired = array_merge($this->commandInterceptorsTxRequired, $this->customPostCommandInterceptorsTxRequired);
            }
            $this->commandInterceptorsTxRequired[] = $this->actualCommandExecutor;
        }
    }

    protected function initCommandInterceptorsTxRequiresNew(): void
    {
        if (empty($this->commandInterceptorsTxRequiresNew)) {
            if (!empty($this->customPreCommandInterceptorsTxRequiresNew)) {
                $this->commandInterceptorsTxRequiresNew = $this->customPreCommandInterceptorsTxRequiresNew;
            } else {
                $this->commandInterceptorsTxRequiresNew = [];
            }
            $this->commandInterceptorsTxRequiresNew = array_merge($this->commandInterceptorsTxRequiresNew, $this->getDefaultCommandInterceptorsTxRequiresNew());
            if (!empty($this->customPostCommandInterceptorsTxRequiresNew)) {
                $this->commandInterceptorsTxRequiresNew = array_merge($this->customPostCommandInterceptorsTxRequiresNew, $this->customPostCommandInterceptorsTxRequiresNew);
            }
            $this->commandInterceptorsTxRequiresNew[] = $this->actualCommandExecutor;
        }
    }

    protected function initCommandExecutorTxRequired(): void
    {
        if ($this->commandExecutorTxRequired === null) {
            $this->commandExecutorTxRequired = $this->initInterceptorChain($this->commandInterceptorsTxRequired);
            $this->commandExecutorTxRequired->setState(...$this->getJobExecutorState());
        }
    }

    protected function initCommandExecutorTxRequiresNew(): void
    {
        if ($this->commandExecutorTxRequiresNew === null) {
            $this->commandExecutorTxRequiresNew = $this->initInterceptorChain($this->commandInterceptorsTxRequiresNew);
            $this->commandExecutorTxRequiresNew->setState(...$this->getJobExecutorState());
        }
    }

    protected function initCommandExecutorDbSchemaOperations(): void
    {
        if ($this->commandExecutorSchemaOperations === null) {
            // in default case, we use the same command executor for DB Schema Operations as for runtime operations.
            // configurations that Use JTA Transactions should override this method and provide a custom command executor
            // that uses NON-JTA Transactions.
            $this->commandExecutorSchemaOperations = $this->commandExecutorTxRequired;
        }
    }

    protected function initInterceptorChain(array $chain): CommandInterceptor
    {
        if (empty($chain)) {
            throw new ProcessEngineException("invalid command interceptor chain configuration: []");
        }
        for ($i = 0; $i < count($chain) - 1; $i += 1) {
            $chain[$i]->setNext($chain[$i + 1]);
        }
        return $chain[0];
    }

    // services /////////////////////////////////////////////////////////////////

    protected function initServices(): void
    {
        $this->initService($this->repositoryService);
        $this->initService($this->runtimeService);
        $this->initService($this->historyService);
        $this->initService($this->identityService);
        $this->initService($this->taskService);
        $this->initService($this->formService);
        $this->initService($this->managementService);
        $this->initService($this->authorizationService);
        //$this->initService(caseService);
        $this->initService($this->filterService);
        $this->initService($this->externalTaskService);
        //$this->initService(decisionService);
        $this->initService($this->optimizeService);
    }

    protected function initService($service): void
    {
        if ($service instanceof ServiceImpl) {
            $service->setCommandExecutor($this->commandExecutorTxRequired);
        }
        if ($service instanceof RepositoryServiceImpl) {
            $service->setDeploymentCharset($this->getDefaultCharset());
        }
    }

    // DataSource ///////////////////////////////////////////////////////////////

    protected function initDataSource(): void
    {
        if ($this->dataSource === null) {
            if (($this->dbDriver === null) || ($this->dbUsername === null)) {
                throw new ProcessEngineException("DataSource properties have to be specified in a process engine configuration");
            }

            $this->dataSource = new UnpooledDataSource($this->dbDriver, $this->dbUrl, $this->dbUsername, $this->dbPassword);

            $props = [];
            if ($this->dbHost !== null) {
                $props['host'] = $this->dbHost;
            }
            if ($this->dbPort !== null) {
                $props['port'] = $this->dbPort;
            }
            if ($this->dbName !== null) {
                $props['dbname'] = $this->dbName;
            }

            if (array_key_exists($this->dbDriver, self::$databaseDriverOptions)) {
                $props[UnpooledDataSource::DRIVER_OPTIONS] = self::$databaseDriverOptions[$this->dbDriver];
            }

            $this->dataSource->setDriverProperties($props);

            //@ATTENTION
            $this->dataSource->setDefaultTransactionIsolationLevel(2);
            //}
        }

        if ($this->databaseType === null) {
            $this->initDatabaseType();
        }
    }

    protected const MY_SQL_PRODUCT_NAME = "mysql";
    protected const MARIA_DB_PRODUCT_NAME = "mysql";
    protected const POSTGRES_DB_PRODUCT_NAME = "postgresql";
    protected static $databaseTypeMappings = [
        self::MY_SQL_PRODUCT_NAME => "mysql",
        self::MARIA_DB_PRODUCT_NAME => "mysql",
        self::POSTGRES_DB_PRODUCT_NAME => "postgres"
    ];
    protected static $databaseDriverOptions = [
        'pdo_mysql' => [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => true,
            \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            UnpooledDataSource::RECONNECT_ATTEMPTS_OPTION => 5,
            UnpooledDataSource::RECONNECT_DELAY_OPTION => 1
        ],
        'pdo_pgsql' => [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => true,
            UnpooledDataSource::RECONNECT_ATTEMPTS_OPTION => 5,
            UnpooledDataSource::RECONNECT_DELAY_OPTION => 1
        ]
    ];

    /*protected static function getDefaultDatabaseTypeMappings(): array
    {
        $databaseTypeMappings = [];
        $databaseTypeMappings->setProperty("H2", "h2");
        databaseTypeMappings->setProperty(MY_SQL_PRODUCT_NAME, "mysql");
        databaseTypeMappings->setProperty(MARIA_DB_PRODUCT_NAME, "mariadb");
        databaseTypeMappings->setProperty("Oracle", "oracle");
        $databaseTypeMappings[self::POSTGRES_DB_PRODUCT_NAME] = "postgres";
        databaseTypeMappings->setProperty(CRDB_DB_PRODUCT_NAME, "cockroachdb");
        databaseTypeMappings->setProperty("Microsoft SQL Server", "mssql");
        databaseTypeMappings->setProperty("DB2", "db2");
        databaseTypeMappings->setProperty("DB2", "db2");
        databaseTypeMappings->setProperty("DB2/NT", "db2");
        databaseTypeMappings->setProperty("DB2/NT64", "db2");
        databaseTypeMappings->setProperty("DB2 UDP", "db2");
        databaseTypeMappings->setProperty("DB2/LINUX", "db2");
        databaseTypeMappings->setProperty("DB2/LINUX390", "db2");
        databaseTypeMappings->setProperty("DB2/LINUXX8664", "db2");
        databaseTypeMappings->setProperty("DB2/LINUXZ64", "db2");
        databaseTypeMappings->setProperty("DB2/400 SQL", "db2");
        databaseTypeMappings->setProperty("DB2/6000", "db2");
        databaseTypeMappings->setProperty("DB2 UDB iSeries", "db2");
        databaseTypeMappings->setProperty("DB2/AIX64", "db2");
        databaseTypeMappings->setProperty("DB2/HPUX", "db2");
        databaseTypeMappings->setProperty("DB2/HP64", "db2");
        databaseTypeMappings->setProperty("DB2/SUN", "db2");
        databaseTypeMappings->setProperty("DB2/SUN64", "db2");
        databaseTypeMappings->setProperty("DB2/PTX", "db2");
        databaseTypeMappings->setProperty("DB2/2", "db2");
        return $databaseTypeMappings;
    }*/

    public function initDatabaseType(): void
    {
        //$connection = null;
        try {
            $connection = $this->dataSource->getConnection();
            $platform = $connection->getDatabasePlatform();
            $databaseProductName = $platform->getName();
            //$connection = $this->dataSource->getConnection();
            //DatabaseMetaData databaseMetaData = connection->getMetaData();
            //String databaseProductName = databaseMetaData->getDatabaseProductName();
            /*if (MY_SQL_PRODUCT_NAME.equals(databaseProductName)) {
                databaseProductName = checkForMariaDb(databaseMetaData, databaseProductName);
            }
            if (POSTGRES_DB_PRODUCT_NAME.equals(databaseProductName)) {
                databaseProductName = checkForCrdb(connection);
            }*/
            //LOG.debugDatabaseproductName(databaseProductName);
            $this->databaseType = self::$databaseTypeMappings[$databaseProductName];
            //ensureNotNull("couldn't deduct database type from database product name '" + databaseProductName + "'", "databaseType", databaseType);
            //LOG.debugDatabaseType(databaseType);

            //$this->initDatabaseVendorAndVersion(databaseMetaData);
        } catch (\Exception $e) {
            //LOG.databaseConnectionAccessException(e);
        } finally {
            /*try {
                if ($connection !== null) {
                    $connection->close();
                }
            } catch (\Exception $e) {
                //LOG.databaseConnectionCloseException(e);
            }*/
        }
    }

    /*protected function initDatabaseVendorAndVersion(DatabaseMetaData $databaseMetaData): void
    {
        databaseVendor = databaseMetaData->getDatabaseProductName();
        databaseVersion = databaseMetaData->getDatabaseProductVersion();
    }*/

    // myBatis SqlSessionFactory ////////////////////////////////////////////////

    protected function initTransactionFactory(): void
    {
        if ($this->transactionFactory === null) {
            if ($this->transactionsExternallyManaged) {
                $this->transactionFactory = new ManagedTransactionFactory();
            } else {
                $this->transactionFactory = new DbalTransactionFactory();
            }
        }
    }

    protected function initSqlSessionFactory(): void
    {
        // to protect access to cachedSqlSessionFactory see CAM-6682
        if ($this->isUseSharedSqlSessionFactory) {
            $this->sqlSessionFactory = $this->cachedSqlSessionFactory;
        }
        if ($this->sqlSessionFactory === null) {
            $inputStream = null;
            //try {
                $inputStream = $this->getMyBatisXmlConfigurationStream();
                // update the dbal parameters to the configured ones...
                $environment = new Environment("default", $this->transactionFactory, $this->dataSource);

                $properties = [];

                if ($this->isUseSharedSqlSessionFactory) {
                    $properties["prefix"] = '${@Jabe\Impl\Context\Context::getProcessEngineConfiguration()->databaseTablePrefix}';
                } else {
                    $properties["prefix"] = $this->databaseTablePrefix;
                }

                self::initSqlSessionFactoryProperties($properties, $this->databaseTablePrefix, $this->databaseType);

                $parser = new MyBatisXMLConfigBuilder($inputStream, "", $properties, [ dirname(dirname(dirname(__DIR__))) ]);
                $configuration = $parser->getConfiguration();
                $configuration->setEnvironment($environment);
                $configuration = $parser->parse();

                $configuration->setDefaultStatementTimeout($this->dbStatementTimeout);

                if ($this->isDbBatchProcessing()) {
                    $configuration->setDefaultExecutorType(ExecutorType::BATCH);
                }

                $this->sqlSessionFactory = new DefaultSqlSessionFactory($configuration);

                if ($this->isUseSharedSqlSessionFactory) {
                    $this->cachedSqlSessionFactory = $this->sqlSessionFactory;
                }
            /*} catch (\Exception $e) {
                throw new ProcessEngineException("Error while building ibatis SqlSessionFactory: " . $e->getMessage());
            } finally {
                IoUtil::closeSilently($inputStream);
            }*/
        }
    }

    public static function initSqlSessionFactoryProperties(array &$properties, ?string $databaseTablePrefix, ?string $databaseType): void
    {
        if ($databaseType !== null) {
            DbSqlSessionFactory::init();
            $properties["limitBefore"] = DbSqlSessionFactory::$databaseSpecificLimitBeforeStatements[$databaseType];
            $properties["limitAfter"] = DbSqlSessionFactory::$databaseSpecificLimitAfterStatements[$databaseType];
            $properties["limitBeforeWithoutOffset"] = DbSqlSessionFactory::$databaseSpecificLimitBeforeWithoutOffsetStatements[$databaseType];
            $properties["limitAfterWithoutOffset"] = DbSqlSessionFactory::$databaseSpecificLimitAfterWithoutOffsetStatements[$databaseType];

            $properties["optimizeLimitBeforeWithoutOffset"] = DbSqlSessionFactory::$optimizeDatabaseSpecificLimitBeforeWithoutOffsetStatements[$databaseType];
            $properties["optimizeLimitAfterWithoutOffset"] = DbSqlSessionFactory::$optimizeDatabaseSpecificLimitAfterWithoutOffsetStatements[$databaseType];
            $properties["innerLimitAfter"] = DbSqlSessionFactory::$databaseSpecificInnerLimitAfterStatements[$databaseType];
            $properties["limitBetween"] = DbSqlSessionFactory::$databaseSpecificLimitBetweenStatements[$databaseType];
            $properties["limitBetweenFilter"] = DbSqlSessionFactory::$databaseSpecificLimitBetweenFilterStatements[$databaseType];
            $properties["limitBetweenAcquisition"] = DbSqlSessionFactory::$databaseSpecificLimitBetweenAcquisitionStatements[$databaseType];
            $properties["orderBy"] = DbSqlSessionFactory::$databaseSpecificOrderByStatements[$databaseType];
            $properties["limitBeforeNativeQuery"] = DbSqlSessionFactory::$databaseSpecificLimitBeforeNativeQueryStatements[$databaseType];
            $properties["distinct"] = DbSqlSessionFactory::$databaseSpecificDistinct[$databaseType];
            $properties["numericCast"] = DbSqlSessionFactory::$databaseSpecificNumericCast[$databaseType];

            $properties["countDistinctBeforeStart"] = DbSqlSessionFactory::$databaseSpecificCountDistinctBeforeStart[$databaseType];
            $properties["countDistinctBeforeEnd"] = DbSqlSessionFactory::$databaseSpecificCountDistinctBeforeEnd[$databaseType];
            $properties["countDistinctAfterEnd"] = DbSqlSessionFactory::$databaseSpecificCountDistinctAfterEnd[$databaseType];

            $properties["escapeChar"] = DbSqlSessionFactory::$databaseSpecificEscapeChar[$databaseType];

            $properties["bitand1"] = DbSqlSessionFactory::$databaseSpecificBitAnd1[$databaseType];
            $properties["bitand2"] = DbSqlSessionFactory::$databaseSpecificBitAnd2[$databaseType];
            $properties["bitand3"] = DbSqlSessionFactory::$databaseSpecificBitAnd3[$databaseType];

            $properties["datepart1"] = DbSqlSessionFactory::$databaseSpecificDatepart1[$databaseType];
            $properties["datepart2"] = DbSqlSessionFactory::$databaseSpecificDatepart2[$databaseType];
            $properties["datepart3"] = DbSqlSessionFactory::$databaseSpecificDatepart3[$databaseType];

            $properties["trueConstant"] = DbSqlSessionFactory::$databaseSpecificTrueConstant[$databaseType];
            $properties["falseConstant"] = DbSqlSessionFactory::$databaseSpecificFalseConstant[$databaseType];

            $properties["dbSpecificDummyTable"] = DbSqlSessionFactory::$databaseSpecificDummyTable[$databaseType];
            $properties["dbSpecificIfNullFunction"] = DbSqlSessionFactory::$databaseSpecificIfNull[$databaseType];

            $properties["dayComparator"] = DbSqlSessionFactory::$databaseSpecificDaysComparator[$databaseType];

            $properties["collationForCaseSensitivity"] = DbSqlSessionFactory::$databaseSpecificCollationForCaseSensitivity[$databaseType];

            $properties["authJoinStart"] = DbSqlSessionFactory::$databaseSpecificAuthJoinStart[$databaseType];
            $properties["authJoinEnd"] = DbSqlSessionFactory::$databaseSpecificAuthJoinEnd[$databaseType];
            $properties["authJoinSeparator"] = DbSqlSessionFactory::$databaseSpecificAuthJoinSeparator[$databaseType];

            $properties["authJoin1Start"] = DbSqlSessionFactory::$databaseSpecificAuth1JoinStart[$databaseType];
            $properties["authJoin1End"] = DbSqlSessionFactory::$databaseSpecificAuth1JoinEnd[$databaseType];
            $properties["authJoin1Separator"] = DbSqlSessionFactory::$databaseSpecificAuth1JoinSeparator[$databaseType];

            $constants = DbSqlSessionFactory::$dbSpecificConstants[$databaseType];
            foreach ($constants as $key => $value) {
                $properties[$key] = $value;
            }
        }
    }

    protected function getMyBatisXmlConfigurationStream()
    {
        $path = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . self::DEFAULT_MYBATIS_MAPPING_FILE;
        try {
            return ReflectUtil::getResourceAsStream($path);
        } catch (\Throwable $t) {
            throw new \Exception(sprintf("Error when parsing mybatis configuration file: %s", $path));
        }
    }

    // session factories ////////////////////////////////////////////////////////

    protected function initIdentityProviderSessionFactory(): void
    {
        if ($this->identityProviderSessionFactory === null) {
            $this->identityProviderSessionFactory = new GenericManagerFactory(DbIdentityServiceProvider::class, ...$this->getJobExecutorState());
        }
    }

    protected function initSessionFactories(): void
    {
        if (empty($this->sessionFactories)) {
            $this->sessionFactories = [];

            $this->initPersistenceProviders();
            $this->addSessionFactory(new DbEntityManagerFactory($this->idGenerator, ...$this->getJobExecutorState()));

            $this->addSessionFactory(new GenericManagerFactory(AttachmentManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(CommentManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(DeploymentManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(ExecutionManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(HistoricActivityInstanceManager::class, ...$this->getJobExecutorState()));
            //$this->addSessionFactory(new GenericManagerFactory(HistoricCaseActivityInstanceManager::class));
            $this->addSessionFactory(new GenericManagerFactory(HistoricStatisticsManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(HistoricDetailManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(HistoricProcessInstanceManager::class, ...$this->getJobExecutorState()));
            //$this->addSessionFactory(new GenericManagerFactory(HistoricCaseInstanceManager::class));
            $this->addSessionFactory(new GenericManagerFactory(UserOperationLogManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(HistoricTaskInstanceManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(HistoricVariableInstanceManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(HistoricIncidentManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(HistoricIdentityLinkLogManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(HistoricJobLogManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(HistoricExternalTaskLogManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(IdentityInfoManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(IdentityLinkManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(JobManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(JobDefinitionManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(ProcessDefinitionManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(PropertyManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(ResourceManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(ByteArrayManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(TableDataManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(TaskManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(TaskReportManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(VariableInstanceManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(EventSubscriptionManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(StatisticsManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(IncidentManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(AuthorizationManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(FilterManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(MeterLogManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(ExternalTaskManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(ReportManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(BatchManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(HistoricBatchManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(TenantManager::class, ...$this->getJobExecutorState()));
            $this->addSessionFactory(new GenericManagerFactory(SchemaLogManager::class, ...$this->getJobExecutorState()));

            /*$this->addSessionFactory(new GenericManagerFactory(CaseDefinitionManager::class));
            $this->addSessionFactory(new GenericManagerFactory(CaseExecutionManager::class));
            $this->addSessionFactory(new GenericManagerFactory(CaseSentryPartManager::class));

            $this->addSessionFactory(new GenericManagerFactory(DecisionDefinitionManager::class));
            $this->addSessionFactory(new GenericManagerFactory(DecisionRequirementsDefinitionManager::class));
            $this->addSessionFactory(new GenericManagerFactory(HistoricDecisionInstanceManager::class));*/

            $this->addSessionFactory(new GenericManagerFactory(FormDefinitionManager::class, ...$this->getJobExecutorState()));

            $this->addSessionFactory(new GenericManagerFactory(OptimizeManager::class, ...$this->getJobExecutorState()));

            $this->sessionFactories[ReadOnlyIdentityProviderInterface::class] = $this->identityProviderSessionFactory;

            // check whether identityProviderSessionFactory implements WritableIdentityProvider
            $identityProviderType = $this->identityProviderSessionFactory->getSessionType();
            if (is_a($identityProviderType, WritableIdentityProviderInterface::class, true)) {
                $this->sessionFactories[WritableIdentityProviderInterface::class] = $this->identityProviderSessionFactory;
            }
        }
        if (!empty($this->customSessionFactories)) {
            foreach ($this->customSessionFactories as $sessionFactory) {
                $this->addSessionFactory($sessionFactory);
            }
        }
    }

    protected function initPersistenceProviders(): void
    {
        $this->ensurePrefixAndSchemaFitToegether($this->databaseTablePrefix, $this->databaseSchema);
        $this->dbSqlSessionFactory = new DbSqlSessionFactory($this->dbBatchProcessing);
        $this->dbSqlSessionFactory->setDatabaseType($this->databaseType);
        $this->dbSqlSessionFactory->setIdGenerator($this->idGenerator);
        $this->dbSqlSessionFactory->setSqlSessionFactory($this->sqlSessionFactory);
        $this->dbSqlSessionFactory->setDbIdentityUsed($this->isDbIdentityUsed);
        $this->dbSqlSessionFactory->setDbHistoryUsed($this->isDbHistoryUsed);
        //$this->dbSqlSessionFactory->setCmmnEnabled(cmmnEnabled);
        //$this->dbSqlSessionFactory->setDmnEnabled(dmnEnabled);
        $this->dbSqlSessionFactory->setDatabaseTablePrefix($this->databaseTablePrefix);

        //hack for the case when schema is defined via databaseTablePrefix parameter and not via databaseSchema parameter
        if ($this->databaseTablePrefix !== null && $this->databaseSchema === null && strpos($this->databaseTablePrefix, ".") !== false) {
            $this->databaseSchema = explode('.', $this->databaseTablePrefix)[0];
        }
        $this->dbSqlSessionFactory->setDatabaseSchema($this->databaseSchema);
        $this->addSessionFactory($this->dbSqlSessionFactory);
        $this->addSessionFactory(new DbSqlPersistenceProviderFactory());
    }

    /*protected function initMigration(): void
    {
        $this->initMigrationInstructionValidators();
        $this->initMigrationActivityMatcher();
        $this->initMigrationInstructionGenerator();
        $this->initMigratingActivityInstanceValidators();
        $this->initMigratingTransitionInstanceValidators();
        $this->initMigratingCompensationInstanceValidators();
    }

    protected function initMigrationActivityMatcher(): void
    {
        if (migrationActivityMatcher === null) {
            migrationActivityMatcher = new DefaultMigrationActivityMatcher();
        }
    }

    protected function initMigrationInstructionGenerator(): void
    {
        if (migrationInstructionGenerator === null) {
            migrationInstructionGenerator = new DefaultMigrationInstructionGenerator(migrationActivityMatcher);
        }

        List<MigrationActivityValidator> migrationActivityValidators = new ArrayList<>();
        if (customPreMigrationActivityValidators !== null) {
            migrationActivityValidators->addAll(customPreMigrationActivityValidators);
        }
        migrationActivityValidators->addAll(getDefaultMigrationActivityValidators());
        if (customPostMigrationActivityValidators !== null) {
            migrationActivityValidators->addAll(customPostMigrationActivityValidators);
        }
        migrationInstructionGenerator = migrationInstructionGenerator
            .migrationActivityValidators(migrationActivityValidators)
            .migrationInstructionValidators(migrationInstructionValidators);
    }

    protected function initMigrationInstructionValidators(): void
    {
        if (migrationInstructionValidators === null) {
            migrationInstructionValidators = new ArrayList<>();
            if (customPreMigrationInstructionValidators !== null) {
                migrationInstructionValidators->addAll(customPreMigrationInstructionValidators);
            }
            migrationInstructionValidators->addAll(getDefaultMigrationInstructionValidators());
            if (customPostMigrationInstructionValidators !== null) {
                migrationInstructionValidators->addAll(customPostMigrationInstructionValidators);
            }
        }
    }

    protected function initMigratingActivityInstanceValidators(): void
    {
        if (migratingActivityInstanceValidators === null) {
            migratingActivityInstanceValidators = new ArrayList<>();
            if (customPreMigratingActivityInstanceValidators !== null) {
                migratingActivityInstanceValidators->addAll(customPreMigratingActivityInstanceValidators);
            }
            migratingActivityInstanceValidators->addAll(getDefaultMigratingActivityInstanceValidators());
            if (customPostMigratingActivityInstanceValidators !== null) {
                migratingActivityInstanceValidators->addAll(customPostMigratingActivityInstanceValidators);
            }
        }
    }

    protected function initMigratingTransitionInstanceValidators(): void
    {
        if (migratingTransitionInstanceValidators === null) {
            migratingTransitionInstanceValidators = new ArrayList<>();
            migratingTransitionInstanceValidators->addAll(getDefaultMigratingTransitionInstanceValidators());
        }
    }

    protected function initMigratingCompensationInstanceValidators(): void
    {
        if (empty($this->migratingCompensationInstanceValidators)) {
            $this->migratingCompensationInstanceValidators = [];

            $this->migratingCompensationInstanceValidators[] = new NoUnmappedLeafInstanceValidator();
            $this->migratingCompensationInstanceValidators[] = new NoUnmappedCompensationStartEventValidator();
        }
    }*/
    /**
     * When providing a schema and a prefix  the prefix has to be the schema ending with a dot.
     */
    protected function ensurePrefixAndSchemaFitToegether(?string $prefix, ?string $schema): void
    {
        if ($schema === null) {
            return;
        } elseif ($prefix === null || ($prefix !== null && strpos($prefix, $schema . ".") !== 0)) {
            throw new ProcessEngineException("When setting a schema the prefix has to be schema + '.'. Received schema: " . $schema . " prefix: " . $prefix);
        }
    }

    protected function addSessionFactory(SessionFactoryInterface $sessionFactory): void
    {
        $this->sessionFactories[$sessionFactory->getSessionType()] = $sessionFactory;
    }

    // deployers ////////////////////////////////////////////////////////////////

    protected function initDeployers(): void
    {
        if (empty($this->deployers)) {
            $this->deployers = [];
            if (!empty($this->customPreDeployers)) {
                $this->deployers = array_merge($this->deployers, $this->customPreDeployers);
            }
            $this->deployers = array_merge($this->deployers, $this->getDefaultDeployers());
            if (!empty($this->customPostDeployers)) {
                $this->deployers = array_merge($this->deployers, $this->customPostDeployers);
            }
        }
        if ($this->deploymentCache === null) {
            $this->deployers = [];
            if (!empty($this->customPreDeployers)) {
                $this->deployers = array_merge($this->deployers, $this->customPreDeployers);
            }
            $this->deployers = array_merge($this->deployers, $this->getDefaultDeployers());
            if (!empty($this->customPostDeployers)) {
                $this->deployers = array_merge($this->deployers, $this->customPostDeployers);
            }

            $this->initCacheFactory();
            $this->deploymentCache = new DeploymentCache($this->cacheFactory, $this->cacheCapacity);
            $this->deploymentCache->setDeployers($this->deployers);
        }
    }

    protected function getDefaultDeployers(): array
    {
        $defaultDeployers = [];

        $bpmnDeployer = $this->getBpmnDeployer();
        $defaultDeployers[] = $bpmnDeployer;

        $defaultDeployers[] = $this->getFormDeployer();

        /*if (isCmmnEnabled()) {
            CmmnDeployer cmmnDeployer = getCmmnDeployer();
            defaultDeployers->add(cmmnDeployer);
        }

        if (isDmnEnabled()) {
            DecisionRequirementsDefinitionDeployer decisionRequirementsDefinitionDeployer = getDecisionRequirementsDefinitionDeployer();
            DecisionDefinitionDeployer decisionDefinitionDeployer = getDecisionDefinitionDeployer();
            // the DecisionRequirementsDefinition cacheDeployer must be before the DecisionDefinitionDeployer
            defaultDeployers->add(decisionRequirementsDefinitionDeployer);
            defaultDeployers->add(decisionDefinitionDeployer);
        }*/

        return $defaultDeployers;
    }

    protected function getBpmnDeployer(): BpmnDeployer
    {
        $bpmnDeployer = new BpmnDeployer();
        $bpmnDeployer->setExpressionManager($this->expressionManager);
        $bpmnDeployer->setIdGenerator($this->idGenerator);

        if ($this->bpmnParseFactory === null) {
            $this->bpmnParseFactory = new DefaultBpmnParseFactory();
        }

        $bpmnParser = new BpmnParser($this->expressionManager, $this->bpmnParseFactory);

        if (!empty($this->preParseListeners)) {
            $bpmnParser->setParseListeners(array_merge($bpmnParser->getParseListeners(), $this->preParseListeners));
        }
        $bpmnParser->setParseListeners(array_merge($bpmnParser->getParseListeners(), $this->getDefaultBPMNParseListeners()));
        if (!empty($this->postParseListeners)) {
            $bpmnParser->setParseListeners(array_merge($bpmnParser->getParseListeners(), $this->postParseListeners));
        }

        $bpmnDeployer->setBpmnParser($bpmnParser);

        return $bpmnDeployer;
    }

    protected function getDefaultBPMNParseListeners(): array
    {
        $defaultListeners = [];
        if (HistoryLevel::historyLevelNone() != $this->historyLevel) {
            $defaultListeners[] = new HistoryParseListener($this->historyEventProducer);
        }
        if ($this->isMetricsEnabled) {
            $defaultListeners[] = new MetricsBpmnParseListener();
        }
        return $defaultListeners;
    }

    protected function getFormDeployer(): FormDefinitionDeployer
    {
        $deployer = new FormDefinitionDeployer();
        $deployer->setIdGenerator($this->idGenerator);
        return $deployer;
    }

    /*
    protected CmmnDeployer getCmmnDeployer() {
        CmmnDeployer cmmnDeployer = new CmmnDeployer();

        cmmnDeployer->setIdGenerator(idGenerator);

        if (cmmnTransformFactory === null) {
            cmmnTransformFactory = new DefaultCmmnTransformFactory();
        }

        if (cmmnElementHandlerRegistry === null) {
            cmmnElementHandlerRegistry = new DefaultCmmnElementHandlerRegistry();
        }

        CmmnTransformer cmmnTransformer = new CmmnTransformer(expressionManager, cmmnElementHandlerRegistry, cmmnTransformFactory);

        if (customPreCmmnTransformListeners !== null) {
            cmmnTransformer->getTransformListeners()->addAll(customPreCmmnTransformListeners);
        }
        cmmnTransformer->getTransformListeners()->addAll(getDefaultCmmnTransformListeners());
        if (customPostCmmnTransformListeners !== null) {
            cmmnTransformer->getTransformListeners()->addAll(customPostCmmnTransformListeners);
        }

        cmmnDeployer->setTransformer(cmmnTransformer);

        return cmmnDeployer;
    }

    protected List<CmmnTransformListener> getDefaultCmmnTransformListeners() {
        List<CmmnTransformListener> defaultListener = new ArrayList<>();
        if (!HistoryLevel.HISTORY_LEVEL_NONE.equals(historyLevel)) {
            defaultListener->add(new CmmnHistoryTransformListener(cmmnHistoryEventProducer));
        }
        if (isMetricsEnabled) {
            defaultListener->add(new MetricsCmmnTransformListener());
        }
        return defaultListener;
    }

    protected DecisionDefinitionDeployer getDecisionDefinitionDeployer() {
        DecisionDefinitionDeployer decisionDefinitionDeployer = new DecisionDefinitionDeployer();
        decisionDefinitionDeployer->setIdGenerator(idGenerator);
        decisionDefinitionDeployer->setTransformer(dmnEngineConfiguration->getTransformer());
        return decisionDefinitionDeployer;
    }

    protected DecisionRequirementsDefinitionDeployer getDecisionRequirementsDefinitionDeployer() {
        DecisionRequirementsDefinitionDeployer drdDeployer = new DecisionRequirementsDefinitionDeployer();
        drdDeployer->setIdGenerator(idGenerator);
        drdDeployer->setTransformer(dmnEngineConfiguration->getTransformer());
        return drdDeployer;
    }

    public function getDmnEngine(): DmnEngine
    {
        return $this->dmnEngine;
    }

    public function setDmnEngine(DmnEngine $dmnEngine): void
    {
        $this->dmnEngine = $dmnEngine;
    }

    public function getDmnEngineConfiguration(): DefaultDmnEngineConfiguration
    {
        return $this->dmnEngineConfiguration;
    }

    public function setDmnEngineConfiguration(DefaultDmnEngineConfiguration $dmnEngineConfiguration): void
    {
        $this->dmnEngineConfiguration = $dmnEngineConfiguration;
    }*/

    // job executor /////////////////////////////////////////////////////////////

    protected function initJobExecutor(): void
    {
        if ($this->jobExecutor === null && !$this->preventJobExecutorInitialization) {
            $this->jobExecutor = new DefaultJobExecutor(...$this->getJobExecutorState());
        }

        $this->jobHandlers = [];
        $timerExecuteNestedActivityJobHandler = new TimerExecuteNestedActivityJobHandler();
        $this->jobHandlers[$timerExecuteNestedActivityJobHandler->getType()] = $timerExecuteNestedActivityJobHandler;

        $timerCatchIntermediateEvent = new TimerCatchIntermediateEventJobHandler();
        $this->jobHandlers[$timerCatchIntermediateEvent->getType()] = $timerCatchIntermediateEvent;

        $timerStartEvent = new TimerStartEventJobHandler();
        $this->jobHandlers[$timerStartEvent->getType()] = $timerStartEvent;

        $timerStartEventSubprocess = new TimerStartEventSubprocessJobHandler();
        $this->jobHandlers[$timerStartEventSubprocess->getType()] = $timerStartEventSubprocess;

        $asyncContinuationJobHandler = new AsyncContinuationJobHandler();
        $this->jobHandlers[$asyncContinuationJobHandler->getType()] = $asyncContinuationJobHandler;

        $processEventJobHandler = new ProcessEventJobHandler();
        $this->jobHandlers[$processEventJobHandler->getType()] = $processEventJobHandler;

        $suspendProcessDefinitionHandler = new TimerSuspendProcessDefinitionHandler();
        $this->jobHandlers[$suspendProcessDefinitionHandler->getType()] = $suspendProcessDefinitionHandler;

        $activateProcessDefinitionHandler = new TimerActivateProcessDefinitionHandler();
        $this->jobHandlers[$activateProcessDefinitionHandler->getType()] = $activateProcessDefinitionHandler;

        $suspendJobDefinitionHandler = new TimerSuspendJobDefinitionHandler();
        $this->jobHandlers[$suspendJobDefinitionHandler->getType()] = $suspendJobDefinitionHandler;

        $activateJobDefinitionHandler = new TimerActivateJobDefinitionHandler();
        $this->jobHandlers[$activateJobDefinitionHandler->getType()] = $activateJobDefinitionHandler;

        $taskListenerJobHandler = new TimerTaskListenerJobHandler();
        $this->jobHandlers[$taskListenerJobHandler->getType()] = $taskListenerJobHandler;

        $batchSeedJobHandler = new BatchSeedJobHandler();
        $this->jobHandlers[$batchSeedJobHandler->getType()] = $batchSeedJobHandler;

        $batchMonitorJobHandler = new BatchMonitorJobHandler();
        $this->jobHandlers[$batchMonitorJobHandler->getType()] = $batchMonitorJobHandler;

        //@TODO
        //$historyCleanupJobHandler = new HistoryCleanupJobHandler();
        //$this->jobHandlers[$historyCleanupJobHandler->getType()] = $historyCleanupJobHandler;

        foreach ($this->batchHandlers as $batchHandler) {
            $this->jobHandlers[$batchHandler->getType()] = $batchHandler;
        }

        // if we have custom job handlers, register them
        if (!empty($this->getCustomJobHandlers())) {
            foreach ($this->getCustomJobHandlers() as $customJobHandler) {
                $this->jobHandlers[$customJobHandler->getType()] = $customJobHandler;
            }
        }

        if (!$this->preventJobExecutorInitialization) {
            $this->jobExecutor->setAutoActivate($this->jobExecutorActivate);
            if ($this->jobExecutor->getRejectedJobsHandler() === null) {
                if ($this->customRejectedJobsHandler !== null) {
                    $this->jobExecutor->setRejectedJobsHandler($this->customRejectedJobsHandler);
                } else {
                    $this->jobExecutor->setRejectedJobsHandler(new NotifyAcquisitionRejectedJobsHandler());
                }
            }
        }

        // verify job executor priority range is configured correctly
        if ($this->jobExecutorPriorityRangeMin > $this->jobExecutorPriorityRangeMax) {
            throw new \Exception("jobExecutorPriorityRangeMin can not be greater than jobExecutorPriorityRangeMax");
            //throw ProcessEngineLogger.JOB_EXECUTOR_LOGGER.jobExecutorPriorityRangeException(
            //    "jobExecutorPriorityRangeMin can not be greater than jobExecutorPriorityRangeMax");
        }
        if ($this->jobExecutorPriorityRangeMin < 0) {
            throw new \Exception("job executor priority range can not be negative");
            //throw ProcessEngineLogger.JOB_EXECUTOR_LOGGER
            //    .jobExecutorPriorityRangeException("job executor priority range can not be negative");
        }

        /*if ($this->jobExecutorPriorityRangeMin > $this->historyCleanupJobPriority || $this->jobExecutorPriorityRangeMax < $this->historyCleanupJobPriority) {
            ProcessEngineLogger.JOB_EXECUTOR_LOGGER.infoJobExecutorDoesNotHandleHistoryCleanupJobs(this);
        }
        if (jobExecutorPriorityRangeMin > batchJobPriority || jobExecutorPriorityRangeMax < batchJobPriority) {
            ProcessEngineLogger.JOB_EXECUTOR_LOGGER.infoJobExecutorDoesNotHandleBatchJobs(this);
        }*/
    }

    protected function initJobProvider(): void
    {
        if ($this->producePrioritizedJobs && $this->jobPriorityProvider === null) {
            $this->jobPriorityProvider = new DefaultJobPriorityProvider();
        }
    }

    //external task /////////////////////////////////////////////////////////////

    protected function initExternalTaskPriorityProvider(): void
    {
        if ($this->producePrioritizedExternalTasks && $this->externalTaskPriorityProvider === null) {
            $this->externalTaskPriorityProvider = new DefaultExternalTaskPriorityProvider();
        }
    }

    // history //////////////////////////////////////////////////////////////////

    public function initHistoryLevel(): void
    {
        if ($this->historyLevel !== null) {
            $this->setHistory($this->historyLevel->getName());
        }

        if (empty($this->historyLevels)) {
            $this->historyLevels = [];
            $this->historyLevels[] = HistoryLevel::historyLevelNone();
            $this->historyLevels[] = HistoryLevel::historyLevelActivity();
            $this->historyLevels[] = HistoryLevel::historyLevelAudit();
            $this->historyLevels[] = HistoryLevel::historyLevelFull();
        }

        if (!empty($this->customHistoryLevels)) {
            $this->historyLevels = array_merge($this->historyLevels, $this->customHistoryLevels);
        }

        if (strtolower(self::HISTORY_VARIABLE) == strtolower($this->history)) {
            $this->historyLevel = HistoryLevel::historyLevelActivity();
            //LOG.usingDeprecatedHistoryLevelVariable();
        } else {
            foreach ($this->historyLevels as $historyLevel) {
                if (strtolower($historyLevel->getName()) == strtolower($this->history)) {
                    $this->historyLevel = $historyLevel;
                }
            }
        }

        // do allow null for history level in case of "auto"
        if ($this->historyLevel === null && strtolower(self::HISTORY_AUTO) != $this->history) {
            throw new ProcessEngineException("invalid history level: " . $this->history);
        }
    }

    // id generator /////////////////////////////////////////////////////////////

    protected function initIdGenerator(): void
    {
        if ($this->idGenerator === null) {
            $idGeneratorCommandExecutor = null;
            if ($this->idGeneratorDataSource !== null) {
                $processEngineConfiguration = new StandaloneProcessEngineConfiguration();
                $processEngineConfiguration->setDataSource($this->idGeneratorDataSource);
                $processEngineConfiguration->setDatabaseSchemaUpdate(self::DB_SCHEMA_UPDATE_FALSE);
                $processEngineConfiguration->init();
                $idGeneratorCommandExecutor = $processEngineConfiguration->getCommandExecutorTxRequiresNew();
            } else {
                $idGeneratorCommandExecutor = $this->commandExecutorTxRequiresNew;
            }
            /*elseif (idGeneratorDataSourceJndiName !== null) {
                ProcessEngineConfigurationImpl processEngineConfiguration = new StandaloneProcessEngineConfiguration();
                processEngineConfiguration->setDataSourceJndiName(idGeneratorDataSourceJndiName);
                processEngineConfiguration->setDatabaseSchemaUpdate(DB_SCHEMA_UPDATE_FALSE);
                processEngineConfiguration.init();
                idGeneratorCommandExecutor = processEngineConfiguration->getCommandExecutorTxRequiresNew();
            }*/

            $dbIdGenerator = new DbIdGenerator();
            $dbIdGenerator->setIdBlockSize($this->idBlockSize);
            $dbIdGenerator->setCommandExecutor($idGeneratorCommandExecutor);
            $this->idGenerator = $dbIdGenerator;
        }
    }

    // OTHER ////////////////////////////////////////////////////////////////////

    protected function initCommandContextFactory(): void
    {
        if ($this->commandContextFactory === null) {
            $this->commandContextFactory = new CommandContextFactory();
            $this->commandContextFactory->setProcessEngineConfiguration($this);
        }
    }

    protected function initTransactionContextFactory(): void
    {
        if ($this->transactionContextFactory === null) {
            $this->transactionContextFactory = new StandaloneTransactionContextFactory();
        }
    }

    protected function initValueTypeResolver(): void
    {
        if ($this->valueTypeResolver === null) {
            $this->valueTypeResolver = new ValueTypeResolverImpl();
        }
    }

    protected function initDefaultCharset(): void
    {
        if ($this->defaultCharset === null) {
            if ($this->defaultCharsetName === null) {
                $this->defaultCharsetName = "UTF-8";
            }
            $this->defaultCharset = $this->defaultCharsetName;
        }
    }

    protected function initMetrics(): void
    {
        if ($this->isMetricsEnabled) {
            if ($this->metricsRegistry === null) {
                $this->metricsRegistry = new MetricsRegistry();
            }

            $this->initDefaultMetrics($this->metricsRegistry);

            if ($this->dbMetricsReporter === null) {
                $this->dbMetricsReporter = new DbMetricsReporter($this->metricsRegistry, $this->commandExecutorTxRequired);
            }
        }
    }

    protected function initHostName(): void
    {
        if ($this->hostname === null) {
            if ($this->hostnameProvider === null) {
                $this->hostnameProvider = new SimpleIpBasedProvider();
            }
            $this->hostname = $this->hostnameProvider->getHostname($this);
        }
    }

    protected function initDefaultMetrics(MetricsRegistry $metricsRegistry): void
    {
        $this->metricsRegistry->createMeter(Metrics::ACTIVTY_INSTANCE_START);
        $this->metricsRegistry->createDbMeter(Metrics::ACTIVTY_INSTANCE_END);

        $this->metricsRegistry->createDbMeter(Metrics::JOB_ACQUISITION_ATTEMPT);
        $this->metricsRegistry->createDbMeter(Metrics::JOB_ACQUIRED_SUCCESS);
        $this->metricsRegistry->createDbMeter(Metrics::JOB_ACQUIRED_FAILURE);
        $this->metricsRegistry->createDbMeter(Metrics::JOB_SUCCESSFUL);
        $this->metricsRegistry->createDbMeter(Metrics::JOB_FAILED);
        $this->metricsRegistry->createDbMeter(Metrics::JOB_LOCKED_EXCLUSIVE);
        $this->metricsRegistry->createDbMeter(Metrics::JOB_EXECUTION_REJECTED);

        $this->metricsRegistry->createMeter(Metrics::ROOT_PROCESS_INSTANCE_START);

        //metricsRegistry.createMeter(Metrics.EXECUTED_DECISION_INSTANCES);
        //metricsRegistry.createMeter(Metrics.EXECUTED_DECISION_ELEMENTS);
    }

    protected function initSerialization(): void
    {
        if (empty($this->variableSerializers)) {
            $this->variableSerializers = new DefaultVariableSerializers();

            if (!empty($this->customPreVariableSerializers)) {
                foreach ($this->customPreVariableSerializers as $customVariableType) {
                    $this->variableSerializers->addSerializer($customVariableType);
                }
            }

            // register built-in serializers
            $this->variableSerializers->addSerializer(new NullValueSerializer());
            $this->variableSerializers->addSerializer(new StringValueSerializer());
            $this->variableSerializers->addSerializer(new BooleanValueSerializer());
            $this->variableSerializers->addSerializer(new IntegerValueSerializer());
            $this->variableSerializers->addSerializer(new DateValueSerializer());
            $this->variableSerializers->addSerializer(new DoubleValueSerializer());
            $this->variableSerializers->addSerializer(new PhpObjectSerializer());
            $this->variableSerializers->addSerializer(new FileValueSerializer());

            if (!empty($this->customPostVariableSerializers)) {
                foreach ($this->customPostVariableSerializers as $customVariableType) {
                    $this->variableSerializers->addSerializer($customVariableType);
                }
            }
        }
    }

    protected function initFormEngines(): void
    {
        if (empty($this->formEngines)) {
            $this->formEngines = [];
            // html form engine = default form engine
            $defaultFormEngine = new HtmlFormEngine();
            //$this->formEngines.put(null, defaultFormEngine); // default form engine is looked up with null
            $this->formEngines[$defaultFormEngine->getName()] = $defaultFormEngine;
            $juelFormEngine = new JuelFormEngine();
            $this->formEngines[$juelFormEngine->getName()] = $juelFormEngine;
        }
        if (!empty($this->customFormEngines)) {
            foreach ($this->customFormEngines as $formEngine) {
                $this->formEngines[$formEngine->getName()] = $formEngine;
            }
        }
    }

    protected function initFormTypes(): void
    {
        if (empty($this->formTypes)) {
            $this->formTypes = new FormTypes();
            $this->formTypes->addFormType(new StringFormType());
            $this->formTypes->addFormType(new IntegerFormType());
            $this->formTypes->addFormType(new DateFormType("dd/MM/yyyy"));
            $this->formTypes->addFormType(new BooleanFormType());
        }
        if (!empty($this->customFormTypes)) {
            foreach ($this->customFormTypes as $customFormType) {
                $this->formTypes->addFormType($customFormType);
            }
        }
    }

    protected function initFormFieldValidators(): void
    {
        if ($this->formValidators === null) {
            $this->formValidators = new FormValidators();
            $this->formValidators->addValidator("min", MinValidator::class);
            $this->formValidators->addValidator("max", MaxValidator::class);
            $this->formValidators->addValidator("minlength", MinLengthValidator::class);
            $this->formValidators->addValidator("maxlength", MaxLengthValidator::class);
            $this->formValidators->addValidator("required", RequiredValidator::class);
            $this->formValidators->addValidator("readonly", ReadOnlyValidator::class);
        }
        if (!empty($this->customFormFieldValidators)) {
            foreach ($this->customFormFieldValidators as $key => $value) {
                $this->formValidators->addValidator($key, $value);
            }
        }
    }

    protected function initScripting(): void
    {
        if (empty($this->resolverFactories)) {
            $this->resolverFactories = [];
            $this->resolverFactories[] = new MocksResolverFactory();
            $this->resolverFactories[] = new VariableScopeResolverFactory();
            $this->resolverFactories[] = new BeansResolverFactory();
        }
        if ($this->scriptEngineResolver === null) {
            $this->scriptEngineResolver = new DefaultScriptEngineResolver(new ScriptEngineManager());
        }
        if ($this->scriptingEngines === null) {
            $this->scriptingEngines = new ScriptingEngines($this->scriptEngineResolver, new ScriptBindingsFactory($this->resolverFactories));
            $this->scriptingEngines->setEnableScriptEngineCaching($this->enableScriptEngineCaching);
        }
        if ($this->scriptFactory === null) {
            $this->scriptFactory = new ScriptFactory();
        }
        if ($this->scriptEnvResolvers === null) {
            $this->scriptEnvResolvers = [];
        }
        if ($this->scriptingEnvironment === null) {
            $this->scriptingEnvironment = new ScriptingEnvironment($this->scriptFactory, $this->scriptEnvResolvers, $this->scriptingEngines);
        }
    }

    /*protected function initDmnEngine(): void
    {
        if (dmnEngine === null) {

            if (dmnEngineConfiguration === null) {
            dmnEngineConfiguration = (DefaultDmnEngineConfiguration) DmnEngineConfiguration.createDefaultDmnEngineConfiguration();
            }

            DmnEngineConfigurationBuilder dmnEngineConfigurationBuilder = new DmnEngineConfigurationBuilder(dmnEngineConfiguration)
                .dmnHistoryEventProducer(dmnHistoryEventProducer)
                .scriptEngineResolver(scriptingEngines)
                .feelCustomFunctionProviders(dmnFeelCustomFunctionProviders)
                .enableFeelLegacyBehavior(dmnFeelEnableLegacyBehavior);

            if (dmnElProvider !== null) {
            dmnEngineConfigurationBuilder.elProvider(dmnElProvider);
            } elseif (expressionManager instanceof ElProviderCompatible) {
            dmnEngineConfigurationBuilder.elProvider(((ElProviderCompatible)expressionManager).toElProvider());
            }

            dmnEngineConfiguration = dmnEngineConfigurationBuilder.build();

            dmnEngine = dmnEngineConfiguration.buildEngine();

        } elseif (dmnEngineConfiguration === null) {
            dmnEngineConfiguration = (DefaultDmnEngineConfiguration) dmnEngine->getConfiguration();
        }
    }*/

    protected function initExpressionManager(): void
    {
        if ($this->expressionManager === null) {
            $this->expressionManager = new JuelExpressionManager($this->beans);
        }

        $this->expressionManager->addFunction(
            CommandContextFunctions::CURRENT_USER,
            ReflectUtil::getMethod(CommandContextFunctions::class, CommandContextFunctions::CURRENT_USER)
        );
        $this->expressionManager->addFunction(
            CommandContextFunctions::CURRENT_USER_GROUPS,
            ReflectUtil::getMethod(CommandContextFunctions::class, CommandContextFunctions::CURRENT_USER_GROUPS)
        );

        $this->expressionManager->addFunction(
            DateTimeFunctions::NOW,
            ReflectUtil::getMethod(DateTimeFunctions::class, DateTimeFunctions::NOW)
        );
        $this->expressionManager->addFunction(
            DateTimeFunctions::DATE_TIME,
            ReflectUtil::getMethod(DateTimeFunctions::class, DateTimeFunctions::DATE_TIME)
        );
    }

    protected function initBusinessCalendarManager(): void
    {
        if ($this->businessCalendarManager === null) {
            $mapBusinessCalendarManager = new MapBusinessCalendarManager();
            $mapBusinessCalendarManager->addBusinessCalendar(DurationBusinessCalendar::NAME, new DurationBusinessCalendar());
            $mapBusinessCalendarManager->addBusinessCalendar(DueDateBusinessCalendar::NAME, new DueDateBusinessCalendar());
            $mapBusinessCalendarManager->addBusinessCalendar(CycleBusinessCalendar::NAME, new CycleBusinessCalendar());
            $this->businessCalendarManager = $mapBusinessCalendarManager;
        }
    }

    protected function initDelegateInterceptor(): void
    {
        if ($this->delegateInterceptor === null) {
            $this->delegateInterceptor = new DefaultDelegateInterceptor();
        }
    }

    protected function initEventHandlers(): void
    {
        if (empty($this->eventHandlers)) {
            $this->eventHandlers = [];

            $signalEventHander = new SignalEventHandler();
            $this->eventHandlers[$signalEventHander->getEventHandlerType()] = $signalEventHander;

            $compensationEventHandler = new CompensationEventHandler();
            $this->eventHandlers[$compensationEventHandler->getEventHandlerType()] = $compensationEventHandler;

            $messageEventHandler = new EventHandlerImpl(EventType::message());
            $this->eventHandlers[$messageEventHandler->getEventHandlerType()] = $messageEventHandler;

            $conditionalEventHandler = new ConditionalEventHandler();
            $this->eventHandlers[$conditionalEventHandler->getEventHandlerType()] = $conditionalEventHandler;
        }
        if (!empty($this->customEventHandlers)) {
            foreach ($this->customEventHandlers as $eventHandler) {
                $this->eventHandlers[$eventHandler->getEventHandlerType()] = $eventHandler;
            }
        }
    }

    protected function initCommandCheckers(): void
    {
        if ($this->commandCheckers === null) {
            $this->commandCheckers = [];

            // add the default command checkers
            $this->commandCheckers[] = new TenantCommandChecker();
            $this->commandCheckers[] = new AuthorizationCommandChecker();
        }
    }

    // JPA //////////////////////////////////////////////////////////////////////

    /*protected function initJpa(): void
    {
        if (jpaPersistenceUnitName !== null) {
            jpaEntityManagerFactory = JpaHelper.createEntityManagerFactory(jpaPersistenceUnitName);
        }
        if (jpaEntityManagerFactory !== null) {
            sessionFactories.put(EntityManagerSession::class, new EntityManagerSessionFactory(jpaEntityManagerFactory, jpaHandleTransaction, jpaCloseEntityManager));
            JPAVariableSerializer jpaType = (JPAVariableSerializer) variableSerializers->getSerializerByName(JPAVariableSerializer.NAME);
            // Add JPA-type
            if (jpaType === null) {
                // We try adding the variable right after byte serializer, if available
                int serializableIndex = variableSerializers->getSerializerIndexByName(ValueType.BYTES->getName());
                if (serializableIndex > -1) {
                    variableSerializers->addSerializer(new JPAVariableSerializer(), serializableIndex);
                } else {
                    variableSerializers->addSerializer(new JPAVariableSerializer());
                }
            }
        }
    }*/

    protected function initBeans(): void
    {
        if ($this->beans === null) {
            $this->beans = $this->defaultBeansMap;
        }
    }

    protected function initArtifactFactory(): void
    {
        if ($this->artifactFactory === null) {
            $this->artifactFactory = new DefaultArtifactFactory();
        }
    }

    protected function initProcessApplicationManager(): void
    {
        if ($this->processApplicationManager === null) {
            $this->processApplicationManager = new ProcessApplicationManager();
        }
    }

    // correlation handler //////////////////////////////////////////////////////
    protected function initCorrelationHandler(): void
    {
        if ($this->correlationHandler === null) {
            $this->correlationHandler = new DefaultCorrelationHandler();
        }
    }

    // condition handler //////////////////////////////////////////////////////
    protected function initConditionHandler(): void
    {
        if ($this->conditionHandler === null) {
            $this->conditionHandler = new DefaultConditionHandler();
        }
    }

    // deployment handler //////////////////////////////////////////////////////
    protected function initDeploymentHandlerFactory(): void
    {
        if ($this->deploymentHandlerFactory === null) {
            $this->deploymentHandlerFactory = new DefaultDeploymentHandlerFactory();
        }
    }

    // history handlers /////////////////////////////////////////////////////

    protected function initHistoryEventProducer(): void
    {
        if ($this->historyEventProducer === null) {
            $this->historyEventProducer = new CacheAwareHistoryEventProducer();
        }
    }

    /*protected function initCmmnHistoryEventProducer(): void
    {
        if (cmmnHistoryEventProducer === null) {
            cmmnHistoryEventProducer = new CacheAwareCmmnHistoryEventProducer();
        }
    }

    protected function initDmnHistoryEventProducer(): void
    {
        if (dmnHistoryEventProducer === null) {
            dmnHistoryEventProducer = new DefaultDmnHistoryEventProducer();
        }
    }*/

    protected function initHistoryEventHandler(): void
    {
        if ($this->historyEventHandler === null) {
            if ($this->enableDefaultDbHistoryEventHandler) {
                $this->historyEventHandler = new CompositeDbHistoryEventHandler($this->customHistoryEventHandlers);
            } else {
                $this->historyEventHandler = new CompositeHistoryEventHandler($this->customHistoryEventHandlers);
            }
        }
    }

    // password digest //////////////////////////////////////////////////////////

    protected function initPasswordDigest(): void
    {
        if ($this->saltGenerator === null) {
            $this->saltGenerator = new Default16ByteSaltGenerator();
        }
        if ($this->passwordEncryptor === null) {
            $this->passwordEncryptor = new Sha512HashDigest();
        }
        if ($this->customPasswordChecker === null) {
            $this->customPasswordChecker = [];
        }
        if ($this->passwordManager === null) {
            $this->passwordManager = new PasswordManager($this->passwordEncryptor, $this->customPasswordChecker);
        }
    }

    public function initPasswordPolicy(): void
    {
        if ($this->passwordPolicy === null && $this->enablePasswordPolicy) {
            $this->passwordPolicy = new DefaultPasswordPolicyImpl();
        }
    }

    protected function initDeploymentRegistration(): void
    {
        if ($this->registeredDeployments === null) {
            $this->registeredDeployments = [];
        }
    }

    // cache factory //////////////////////////////////////////////////////////

    protected function initCacheFactory(): void
    {
        if ($this->cacheFactory === null) {
            $this->cacheFactory = new DefaultCacheFactory();
        }
    }

    // resource authorization provider //////////////////////////////////////////

    protected function initResourceAuthorizationProvider(): void
    {
        if ($this->resourceAuthorizationProvider === null) {
            $this->resourceAuthorizationProvider = new DefaultAuthorizationProvider();
        }
    }

    protected function initPermissionProvider(): void
    {
        if ($this->permissionProvider === null) {
            $this->permissionProvider = new DefaultPermissionProvider();
        }
    }

    protected function initDefaultUserPermissionForTask(): void
    {
        if ($this->defaultUserPermissionForTask === null) {
            if (Permissions::update()->getName() == $this->defaultUserPermissionNameForTask) {
                $this->defaultUserPermissionForTask = Permissions::update();
            } elseif (Permissions::taskWork()->getName() == $this->defaultUserPermissionNameForTask) {
                $this->defaultUserPermissionForTask = Permissions::taskWork();
            } else {
                //throw LOG.invalidConfigDefaultUserPermissionNameForTask(defaultUserPermissionNameForTask, new String[]{Permissions.UPDATE->getName(), Permissions.TASK_WORK->getName()});
                throw new \Exception("invalidConfigDefaultUserPermissionNameForTask");
            }
        }
    }

    protected function initAdminUser(): void
    {
        if ($this->adminUsers === null) {
            $this->adminUsers = [];
        }
    }

    protected function initAdminGroups(): void
    {
        if (empty($this->adminGroups)) {
            $this->adminGroups = [];
        }
        if (!in_array(GroupsInterface::ADMIN, $this->adminGroups)) {
            $this->adminGroups[] = GroupsInterface::ADMIN;
        }
    }

    /*protected function initTelemetry(): void
    {
        if (telemetryRegistry === null) {
            telemetryRegistry = new TelemetryRegistry();
        }
        if (telemetryData === null) {
            $this->initTelemetryData();
        }
        try {
            if (telemetryHttpConnector === null) {
                telemetryHttpConnector = Connectors->getConnector(Connectors.HTTP_CONNECTOR_ID);
            }
        } catch (Exception $e) {
            ProcessEngineLogger.TELEMETRY_LOGGER.unexpectedExceptionDuringHttpConnectorConfiguration(e);
        }
        if (telemetryHttpConnector === null) {
            ProcessEngineLogger.TELEMETRY_LOGGER.unableToConfigureHttpConnectorWarning();
        } else {
            if (telemetryReporter === null) {
                telemetryReporter = new TelemetryReporter(commandExecutorTxRequired,
                                                            telemetryEndpoint,
                                                            telemetryRequestRetries,
                                                            telemetryReportingPeriod,
                                                            telemetryData,
                                                            telemetryHttpConnector,
                                                            telemetryRegistry,
                                                            metricsRegistry,
                                                            telemetryRequestTimeout);
            }
        }
    }

    protected function initTelemetryData(): void
    {
        DatabaseImpl database = new DatabaseImpl(databaseVendor, databaseVersion);

        JdkImpl jdk = ParseUtil.parseJdkDetails();

        InternalsImpl internals = new InternalsImpl(database, telemetryRegistry->getApplicationServer(), telemetryRegistry->getLicenseKey(), jdk);
        internals->setDataCollectionStartDate(ClockUtil::getCurrentTime());

        String camundaIntegration = telemetryRegistry->getCamundaIntegration();
        if (camundaIntegration !== null && !camundaIntegration.isEmpty()) {
            internals->getCamundaIntegration()->add(camundaIntegration);
        }

        ProcessEngineDetails engineInfo = ParseUtil
            .parseProcessEngineVersion(true);

        ProductImpl product = new ProductImpl(PRODUCT_NAME, engineInfo->getVersion(), engineInfo->getEdition(), internals);

        // installationId=null, the id will be fetched later from database
        telemetryData = new TelemetryDataImpl(null, product);
    }*/

    // getters and setters //////////////////////////////////////////////////////

    public function getProcessEngineName(): ?string
    {
        return $this->processEngineName;
    }

    public function getHistoryLevel(): HistoryLevelInterface
    {
        return $this->historyLevel;
    }

    public function setHistoryLevel(HistoryLevelInterface $historyLevel): void
    {
        $this->historyLevel = $historyLevel;
    }

    public function getDefaultHistoryLevel(): HistoryLevelInterface
    {
        if (!empty($this->historyLevels)) {
            foreach ($this->historyLevels as $historyLevel) {
                if (self::HISTORY_DEFAULT !== null && strtolwer(self::HISTORY_DEFAULT) == strtolower($historyLevel->getName())) {
                    return $historyLevel;
                }
            }
        }

        return null;
    }

    public function setProcessEngineName(?string $processEngineName): ProcessEngineConfigurationImpl
    {
        $this->processEngineName = $processEngineName;
        return $this;
    }

    public function getCustomPreCommandInterceptorsTxRequired(): array
    {
        return $this->customPreCommandInterceptorsTxRequired;
    }

    public function setCustomPreCommandInterceptorsTxRequired(array $customPreCommandInterceptorsTxRequired): ProcessEngineConfigurationImpl
    {
        $this->customPreCommandInterceptorsTxRequired = $customPreCommandInterceptorsTxRequired;
        return $this;
    }

    public function getCustomPostCommandInterceptorsTxRequired(): array
    {
        return $this->customPostCommandInterceptorsTxRequired;
    }

    public function setCustomPostCommandInterceptorsTxRequired(array $customPostCommandInterceptorsTxRequired): ProcessEngineConfigurationImpl
    {
        $this->customPostCommandInterceptorsTxRequired = $customPostCommandInterceptorsTxRequired;
        return $this;
    }

    public function getCommandInterceptorsTxRequired(): array
    {
        return $this->commandInterceptorsTxRequired;
    }

    public function setCommandInterceptorsTxRequired(array $commandInterceptorsTxRequired): ProcessEngineConfigurationImpl
    {
        $this->commandInterceptorsTxRequired = $commandInterceptorsTxRequired;
        return $this;
    }

    public function getCommandExecutorTxRequired(): CommandExecutorInterface
    {
        return $this->commandExecutorTxRequired;
    }

    public function setCommandExecutorTxRequired(CommandExecutorInterface $commandExecutorTxRequired): ProcessEngineConfigurationImpl
    {
        $this->commandExecutorTxRequired = $commandExecutorTxRequired;
        $this->commandExecutorTxRequired->setState(...$this->getJobExecutorState());
        return $this;
    }

    public function getCustomPreCommandInterceptorsTxRequiresNew(): array
    {
        return $this->customPreCommandInterceptorsTxRequiresNew;
    }

    public function setCustomPreCommandInterceptorsTxRequiresNew(array $customPreCommandInterceptorsTxRequiresNew): ProcessEngineConfigurationImpl
    {
        $this->customPreCommandInterceptorsTxRequiresNew = $customPreCommandInterceptorsTxRequiresNew;
        return $this;
    }

    public function getCustomPostCommandInterceptorsTxRequiresNew(): array
    {
        return $this->customPostCommandInterceptorsTxRequiresNew;
    }

    public function setCustomPostCommandInterceptorsTxRequiresNew(array $customPostCommandInterceptorsTxRequiresNew): ProcessEngineConfigurationImpl
    {
        $this->customPostCommandInterceptorsTxRequiresNew = $customPostCommandInterceptorsTxRequiresNew;
        return $this;
    }

    public function getCommandInterceptorsTxRequiresNew(): array
    {
        return $this->commandInterceptorsTxRequiresNew;
    }

    public function setCommandInterceptorsTxRequiresNew(array $commandInterceptorsTxRequiresNew): ProcessEngineConfigurationImpl
    {
        $this->commandInterceptorsTxRequiresNew = $commandInterceptorsTxRequiresNew;
        return $this;
    }

    public function getCommandExecutorTxRequiresNew(): CommandExecutorInterface
    {
        return $this->commandExecutorTxRequiresNew;
    }

    public function setCommandExecutorTxRequiresNew(CommandExecutorInterface $commandExecutorTxRequiresNew): ProcessEngineConfigurationImpl
    {
        $this->commandExecutorTxRequiresNew = $commandExecutorTxRequiresNew;
        $this->commandExecutorTxRequiresNew->setState(...$this->getJobExecutorState());
        return $this;
    }

    public function getRepositoryService(): RepositoryServiceInterface
    {
        return $this->repositoryService;
    }

    public function setRepositoryService(RepositoryServiceInterface $repositoryService): ProcessEngineConfigurationImpl
    {
        $this->repositoryService = $repositoryService;
        return $this;
    }

    public function getRuntimeService(): RuntimeServiceInterface
    {
        return $this->runtimeService;
    }

    public function setRuntimeService(RuntimeServiceInterface $runtimeService): ProcessEngineConfigurationImpl
    {
        $this->runtimeService = $runtimeService;
        return $this;
    }

    public function getHistoryService(): HistoryServiceInterface
    {
        return $this->historyService;
    }

    public function setHistoryService(HistoryServiceInterface $historyService): ProcessEngineConfigurationImpl
    {
        $this->historyService = $historyService;
        return $this;
    }

    public function getIdentityService(): IdentityServiceInterface
    {
        return $this->identityService;
    }

    public function setIdentityService(IdentityServiceInterface $identityService): ProcessEngineConfigurationImpl
    {
        $this->identityService = $identityService;
        return $this;
    }

    public function getTaskService(): TaskServiceInterface
    {
        return $this->taskService;
    }

    public function setTaskService(TaskServiceInterface $taskService): ProcessEngineConfigurationImpl
    {
        $this->taskService = $taskService;
        return $this;
    }

    public function getFormService(): FormServiceInterface
    {
        return $this->formService;
    }

    public function setFormService(FormServiceInterface $formService): ProcessEngineConfigurationImpl
    {
        $this->formService = $formService;
        return $this;
    }

    public function getManagementService(): ManagementServiceInterface
    {
        return $this->managementService;
    }

    public function getAuthorizationService(): AuthorizationServiceInterface
    {
        return $this->authorizationService;
    }

    public function setAuthorizationService(AuthorizationServiceInterface $authorizationService): void
    {
        $this->authorizationService = $authorizationService;
    }

    public function setManagementService(ManagementServiceInterface $managementService): ProcessEngineConfigurationImpl
    {
        $this->managementService = $managementService;
        return $this;
    }

    /*public function getCaseService(): CaseService
    {
        return $this->caseService;
    }

    public function setCaseService(CaseService $caseService): void
    {
        $this->caseService = $caseService;
    }*/

    public function getFilterService(): FilterServiceInterface
    {
        return $this->filterService;
    }

    public function setFilterService(FilterServiceInterface $filterService): void
    {
        $this->filterService = $filterService;
    }

    public function getExternalTaskService(): ExternalTaskServiceInterface
    {
        return $this->externalTaskService;
    }

    public function setExternalTaskService(ExternalTaskServiceInterface $externalTaskService): void
    {
        $this->externalTaskService = $externalTaskService;
    }

    /*public function getDecisionService(): DecisionService
    {
        return $this->decisionService;
    }

    public function setDecisionService(DecisionService $decisionService): void
    {
        $this->decisionService = $decisionService;
    }*/

    public function getOptimizeService(): OptimizeService
    {
        return $this->optimizeService;
    }

    public function getSessionFactories(): array
    {
        return $this->sessionFactories;
    }

    public function setSessionFactories(array $sessionFactories): ProcessEngineConfigurationImpl
    {
        $this->sessionFactories = $sessionFactories;
        return $this;
    }

    public function getDeployers(): array
    {
        return $this->deployers;
    }

    public function setDeployers(array $deployers): ProcessEngineConfigurationImpl
    {
        $this->deployers = $deployers;
        return $this;
    }

    public function getJobExecutor(): ?JobExecutor
    {
        return $this->jobExecutor;
    }

    public function setJobExecutor(JobExecutor $jobExecutor): ProcessEngineConfigurationImpl
    {
        $this->jobExecutor = $jobExecutor;
        return $this;
    }

    public function getJobPriorityProvider(): PriorityProviderInterface
    {
        return $this->jobPriorityProvider;
    }

    public function setJobPriorityProvider(PriorityProviderInterface $jobPriorityProvider): void
    {
        $this->jobPriorityProvider = $jobPriorityProvider;
    }

    public function getJobExecutorPriorityRangeMin(): int
    {
        return $this->jobExecutorPriorityRangeMin;
    }

    public function setJobExecutorPriorityRangeMin(int $jobExecutorPriorityRangeMin): ProcessEngineConfigurationImpl
    {
        $this->jobExecutorPriorityRangeMin = $jobExecutorPriorityRangeMin;
        return $this;
    }

    public function getJobExecutorPriorityRangeMax(): int
    {
        return $this->jobExecutorPriorityRangeMax;
    }

    public function setJobExecutorPriorityRangeMax(int $jobExecutorPriorityRangeMax): ProcessEngineConfigurationImpl
    {
        $this->jobExecutorPriorityRangeMax = $jobExecutorPriorityRangeMax;
        return $this;
    }

    public function getExternalTaskPriorityProvider(): PriorityProviderInterface
    {
        return $this->externalTaskPriorityProvider;
    }

    public function setExternalTaskPriorityProvider(PriorityProviderInterface $externalTaskPriorityProvider): void
    {
        $this->externalTaskPriorityProvider = $externalTaskPriorityProvider;
    }

    public function getIdGenerator(): IdGeneratorInterface
    {
        return $this->idGenerator;
    }

    public function setIdGenerator(IdGeneratorInterface $idGenerator): ProcessEngineConfigurationImpl
    {
        $this->idGenerator = $idGenerator;
        return $this;
    }

    public function getWsSyncFactoryClassName(): ?string
    {
        return $this->wsSyncFactoryClassName;
    }

    public function setWsSyncFactoryClassName(?string $wsSyncFactoryClassName): ProcessEngineConfigurationImpl
    {
        $this->wsSyncFactoryClassName = $wsSyncFactoryClassName;
        return $this;
    }

    public function getFormEngines(): array
    {
        return $this->formEngines;
    }

    public function setFormEngines(array $formEngines): ProcessEngineConfigurationImpl
    {
        $this->formEngines = $formEngines;
        return $this;
    }

    public function getFormTypes(): FormTypes
    {
        return $this->formTypes;
    }

    public function setFormTypes(FormTypes $formTypes): ProcessEngineConfigurationImpl
    {
        $this->formTypes = $formTypes;
        return $this;
    }

    public function getScriptingEngines(): ScriptingEngines
    {
        return $this->scriptingEngines;
    }

    public function setScriptingEngines(ScriptingEngines $scriptingEngines): ProcessEngineConfigurationImpl
    {
        $this->scriptingEngines = $scriptingEngines;
        return $this;
    }

    public function getVariableSerializers(): VariableSerializersInterface
    {
        return $this->variableSerializers;
    }

    public function getFallbackSerializerFactory(): ?VariableSerializerFactoryInterface
    {
        return $this->fallbackSerializerFactory;
    }

    public function setFallbackSerializerFactory(VariableSerializerFactoryInterface $fallbackSerializerFactory): void
    {
        $this->fallbackSerializerFactory = $fallbackSerializerFactory;
    }

    public function setVariableTypes(VariableSerializersInterface $variableSerializers): ProcessEngineConfigurationImpl
    {
        $this->variableSerializers = $variableSerializers;
        return $this;
    }

    public function getExpressionManager(): ExpressionManagerInterface
    {
        return $this->expressionManager;
    }

    public function setExpressionManager(ExpressionManagerInterface $expressionManager): ProcessEngineConfigurationImpl
    {
        $this->expressionManager = $expressionManager;
        return $this;
    }

    /*public function getDmnElProvider(): ElProvider
    {
        return $this->dmnElProvider;
    }

    public function setDmnElProvider(ElProvider $elProvider): ProcessEngineConfigurationImpl
    {
        $this->dmnElProvider = $elProvider;
        return $this;
    }*/

    public function getBusinessCalendarManager(): BusinessCalendarManagerInterface
    {
        return $this->businessCalendarManager;
    }

    public function setBusinessCalendarManager(BusinessCalendarManagerInterface $businessCalendarManager): ProcessEngineConfigurationImpl
    {
        $this->businessCalendarManager = $businessCalendarManager;
        return $this;
    }

    public function getCommandContextFactory(): CommandContextFactory
    {
        return $this->commandContextFactory;
    }

    public function setCommandContextFactory(CommandContextFactory $commandContextFactory): ProcessEngineConfigurationImpl
    {
        $this->commandContextFactory = $commandContextFactory;
        return $this;
    }

    public function getTransactionContextFactory(): TransactionContextFactoryInterface
    {
        return $this->transactionContextFactory;
    }

    public function setTransactionContextFactory(TransactionContextFactoryInterface $transactionContextFactory): ProcessEngineConfigurationImpl
    {
        $this->transactionContextFactory = $transactionContextFactory;
        return $this;
    }

    public function getBpmnParseFactory(): BpmnParseFactory
    {
        return $this->bpmnParseFactory;
    }

    public function setBpmnParseFactory(BpmnParseFactory $bpmnParseFactory): ProcessEngineConfigurationImpl
    {
        $this->bpmnParseFactory = $bpmnParseFactory;
        return $this;
    }

    public function getCustomPreDeployers(): array
    {
        return $this->customPreDeployers;
    }

    public function setCustomPreDeployers(array $customPreDeployers): ProcessEngineConfigurationImpl
    {
        $this->customPreDeployers = $customPreDeployers;
        return $this;
    }

    public function getCustomPostDeployers(): array
    {
        return $this->customPostDeployers;
    }

    public function setCustomPostDeployers(array $customPostDeployers): ProcessEngineConfigurationImpl
    {
        $this->customPostDeployers = $customPostDeployers;
        return $this;
    }

    public function setCacheFactory(CacheFactoryInterface $cacheFactory): void
    {
        $this->cacheFactory = $cacheFactory;
    }

    public function setCacheCapacity(int $cacheCapacity): void
    {
        $this->cacheCapacity = $cacheCapacity;
    }

    public function setEnableFetchProcessDefinitionDescription(bool $enableFetchProcessDefinitionDescription): void
    {
        $this->enableFetchProcessDefinitionDescription = $enableFetchProcessDefinitionDescription;
    }

    public function getEnableFetchProcessDefinitionDescription(): bool
    {
        return $this->enableFetchProcessDefinitionDescription;
    }

    public function getDefaultUserPermissionForTask(): Permission
    {
        return $this->defaultUserPermissionForTask;
    }

    public function setDefaultUserPermissionForTask(Permission $defaultUserPermissionForTask): ProcessEngineConfigurationImpl
    {
        $this->defaultUserPermissionForTask = $defaultUserPermissionForTask;
        return $this;
    }

    public function setEnableHistoricInstancePermissions(bool $enable): ProcessEngineConfigurationImpl
    {
        $this->enableHistoricInstancePermissions = $enable;
        return $this;
    }

    public function isEnableHistoricInstancePermissions(): bool
    {
        return $this->enableHistoricInstancePermissions;
    }

    public function getJobHandlers(): array
    {
        return $this->jobHandlers;
    }

    public function setJobHandlers(array $jobHandlers): ProcessEngineConfigurationImpl
    {
        $this->jobHandlers = $jobHandlers;
        return $this;
    }

    public function getSqlSessionFactory(): SqlSessionFactoryInterface
    {
        return $this->sqlSessionFactory;
    }

    public function setSqlSessionFactory(SqlSessionFactoryInterface $sqlSessionFactory): ProcessEngineConfigurationImpl
    {
        $this->sqlSessionFactory = $sqlSessionFactory;
        return $this;
    }

    public function getDbSqlSessionFactory(): DbSqlSessionFactory
    {
        return $this->dbSqlSessionFactory;
    }

    public function setDbSqlSessionFactory(DbSqlSessionFactory $dbSqlSessionFactory): ProcessEngineConfigurationImpl
    {
        $this->dbSqlSessionFactory = $dbSqlSessionFactory;
        return $this;
    }

    public function getTransactionFactory(): TransactionFactoryInterface
    {
        return $this->transactionFactory;
    }

    public function setTransactionFactory(TransactionFactoryInterface $transactionFactory): ProcessEngineConfigurationImpl
    {
        $this->transactionFactory = $transactionFactory;
        return $this;
    }

    public function getCustomSessionFactories(): array
    {
        return $this->customSessionFactories;
    }

    public function setCustomSessionFactories(array $customSessionFactories): ProcessEngineConfigurationImpl
    {
        $this->customSessionFactories = $customSessionFactories;
        return $this;
    }

    public function getCustomJobHandlers(): array
    {
        return $this->customJobHandlers;
    }

    public function setCustomJobHandlers(array $customJobHandlers): ProcessEngineConfigurationImpl
    {
        $this->customJobHandlers = $customJobHandlers;
        return $this;
    }

    public function getCustomFormEngines(): array
    {
        return $this->customFormEngines;
    }

    public function setCustomFormEngines(array $customFormEngines): ProcessEngineConfigurationImpl
    {
        $this->customFormEngines = $customFormEngines;
        return $this;
    }

    public function getCustomFormTypes(): array
    {
        return $this->customFormTypes;
    }

    public function setCustomFormTypes(array $customFormTypes): ProcessEngineConfigurationImpl
    {
        $this->customFormTypes = $customFormTypes;
        return $this;
    }

    public function getCustomPreVariableSerializers(): array
    {
        return $this->customPreVariableSerializers;
    }

    public function setCustomPreVariableSerializers(array $customPreVariableTypes): ProcessEngineConfigurationImpl
    {
        $this->customPreVariableSerializers = $customPreVariableTypes;
        return $this;
    }

    public function getCustomPostVariableSerializers(): array
    {
        return $this->customPostVariableSerializers;
    }

    public function setCustomPostVariableSerializers(array $customPostVariableTypes): ProcessEngineConfigurationImpl
    {
        $this->customPostVariableSerializers = $customPostVariableTypes;
        return $this;
    }

    public function getCustomPreBPMNParseListeners(): array
    {
        return $this->preParseListeners;
    }

    public function setCustomPreBPMNParseListeners(array $preParseListeners): void
    {
        $this->preParseListeners = $preParseListeners;
    }

    public function getCustomPostBPMNParseListeners(): array
    {
        return $this->postParseListeners;
    }

    public function setCustomPostBPMNParseListeners(array $postParseListeners): void
    {
        $this->postParseListeners = $postParseListeners;
    }

    /*public function getCustomPreCmmnTransformListeners(): array
    {
        return customPreCmmnTransformListeners;
    }

    public function setCustomPreCmmnTransformListeners(array $customPreCmmnTransformListeners): void
    {
        $this->customPreCmmnTransformListeners = $customPreCmmnTransformListeners;
    }

    public function getCustomPostCmmnTransformListeners(): array
    {
        return customPostCmmnTransformListeners;
    }

    public function setCustomPostCmmnTransformListeners(array $customPostCmmnTransformListeners): void
    {
        $this->customPostCmmnTransformListeners = $customPostCmmnTransformListeners;
    }*/

    public function getBeans(): array
    {
        return $this->beans;
    }

    public function setBeans(array $beans): void
    {
        $this->beans = $beans;
    }

    public function setDatabaseType(?string $databaseType): ProcessEngineConfigurationImpl
    {
        parent::setDatabaseType($databaseType);
        return $this;
    }

    public function setDataSource(DataSourceInterface $dataSource): ProcessEngineConfigurationImpl
    {
        parent::setDataSource($dataSource);
        return $this;
    }

    public function setDatabaseSchemaUpdate(?string $databaseSchemaUpdate): ProcessEngineConfigurationImpl
    {
        parent::setDatabaseSchemaUpdate($databaseSchemaUpdate);
        return $this;
    }

    public function setHistory(?string $history): ProcessEngineConfigurationImpl
    {
        parent::setHistory($history);
        return $this;
    }

    public function setIdBlockSize(int $idBlockSize): ProcessEngineConfigurationImpl
    {
        parent::setIdBlockSize($idBlockSize);
        return $this;
    }

    public function setDbalDriver(?string $dbalDriver): ProcessEngineConfigurationImpl
    {
        parent::setDbalDriver($dbalDriver);
        return $this;
    }

    public function setDbalPassword(?string $dbalPassword): ProcessEngineConfigurationImpl
    {
        parent::setDbalPassword($dbalPassword);
        return $this;
    }

    public function setDbalUrl(?string $dbalUrl): ProcessEngineConfigurationImpl
    {
        parent::setDbalUrl($dbalUrl);
        return $this;
    }

    public function setDbalUsername(?string $dbalUsername): ProcessEngineConfigurationImpl
    {
        parent::setDbalUsername($dbalUsername);
        return $this;
    }

    public function setJobExecutorActivate(bool $jobExecutorActivate): ProcessEngineConfigurationImpl
    {
        parent::setJobExecutorActivate($jobExecutorActivate);
        return $this;
    }

    public function setMailServerDefaultFrom(?string $mailServerDefaultFrom): ProcessEngineConfigurationImpl
    {
        parent::setMailServerDefaultFrom($mailServerDefaultFrom);
        return $this;
    }

    public function setMailServerHost(?string $mailServerHost): ProcessEngineConfigurationImpl
    {
        parent::setMailServerHost($mailServerHost);
        return $this;
    }

    public function setMailServerPassword(?string $mailServerPassword): ProcessEngineConfigurationImpl
    {
        parent::setMailServerPassword($mailServerPassword);
        return $this;
    }

    public function setMailServerPort(int $mailServerPort): ProcessEngineConfigurationImpl
    {
        parent::setMailServerPort($mailServerPort);
        return $this;
    }

    public function setMailServerUseTLS(bool $useTLS): ProcessEngineConfigurationImpl
    {
        parent::setMailServerUseTLS($useTLS);
        return $this;
    }

    public function setMailServerUsername(?string $mailServerUsername): ProcessEngineConfigurationImpl
    {
        parent::setMailServerUsername($mailServerUsername);
        return $this;
    }

    public function setDbalMaxActiveConnections(int $dbalMaxActiveConnections): ProcessEngineConfigurationImpl
    {
        parent::setDbalMaxActiveConnections($dbalMaxActiveConnections);
        return $this;
    }

    public function setDbalMaxCheckoutTime(int $dbalMaxCheckoutTime): ProcessEngineConfigurationImpl
    {
        parent::setDbalMaxCheckoutTime($dbalMaxCheckoutTime);
        return $this;
    }

    public function setDbalMaxIdleConnections(int $dbalMaxIdleConnections): ProcessEngineConfigurationImpl
    {
        parent::setDbalMaxIdleConnections($dbalMaxIdleConnections);
        return $this;
    }

    public function setDbalMaxWaitTime(int $dbalMaxWaitTime): ProcessEngineConfigurationImpl
    {
        parent::setDbalMaxWaitTime($dbalMaxWaitTime);
        return $this;
    }

    public function setTransactionsExternallyManaged(bool $transactionsExternallyManaged): ProcessEngineConfigurationImpl
    {
        parent::setTransactionsExternallyManaged($transactionsExternallyManaged);
        return $this;
    }

    /*public function setJpaEntityManagerFactory(Object $jpaEntityManagerFactory): ProcessEngineConfigurationImpl
    {
        $this->jpaEntityManagerFactory = $jpaEntityManagerFactory;
        return $this;
    }

    public function setJpaHandleTransaction(bool $jpaHandleTransaction): ProcessEngineConfigurationImpl
    {
        $this->jpaHandleTransaction = $jpaHandleTransaction;
        return $this;
    }

    public function setJpaCloseEntityManager(bool $jpaCloseEntityManager): ProcessEngineConfigurationImpl
    {
        $this->jpaCloseEntityManager = $jpaCloseEntityManager;
        return $this;
    }*/

    public function setDbalPingEnabled(bool $dbPingEnabled): ProcessEngineConfigurationImpl
    {
        $this->dbPingEnabled = $dbPingEnabled;
        return $this;
    }

    public function setDbalPingQuery(?string $dbPingQuery): ProcessEngineConfigurationImpl
    {
        $this->dbPingQuery = $dbPingQuery;
        return $this;
    }

    public function setDbalPingConnectionNotUsedFor(int $dbalPingNotUsedFor): ProcessEngineConfigurationImpl
    {
        $this->dbPingConnectionNotUsedFor = $dbalPingNotUsedFor;
        return $this;
    }

    public function isDbIdentityUsed(): bool
    {
        return $this->isDbIdentityUsed;
    }

    public function setDbIdentityUsed(bool $isDbIdentityUsed): void
    {
        $this->isDbIdentityUsed = $isDbIdentityUsed;
    }

    public function isDbHistoryUsed(): bool
    {
        return $this->isDbHistoryUsed;
    }

    public function setDbHistoryUsed(bool $isDbHistoryUsed): void
    {
        $this->isDbHistoryUsed = $isDbHistoryUsed;
    }

    public function getResolverFactories(): array
    {
        return $this->resolverFactories;
    }

    public function setResolverFactories(array $resolverFactories): void
    {
        $this->resolverFactories = $resolverFactories;
    }

    public function getDeploymentCache(): DeploymentCache
    {
        return $this->deploymentCache;
    }

    public function setDeploymentCache(DeploymentCache $deploymentCache): void
    {
        $this->deploymentCache = $deploymentCache;
    }

    public function getDeploymentHandlerFactory(): DeploymentHandlerFactoryInterface
    {
        return $this->deploymentHandlerFactory;
    }

    public function setDeploymentHandlerFactory(DeploymentHandlerFactoryInterface $deploymentHandlerFactory): ProcessEngineConfigurationImpl
    {
        $this->deploymentHandlerFactory = $deploymentHandlerFactory;
        return $this;
    }

    public function setDelegateInterceptor(DelegateInterceptorInterface $delegateInterceptor): ProcessEngineConfigurationImpl
    {
        $this->delegateInterceptor = $delegateInterceptor;
        return $this;
    }

    public function getDelegateInterceptor(): DelegateInterceptorInterface
    {
        return $this->delegateInterceptor;
    }

    public function getCustomRejectedJobsHandler(): RejectedJobsHandlerInterface
    {
        return $this->customRejectedJobsHandler;
    }

    public function setCustomRejectedJobsHandler(RejectedJobsHandlerInterface $customRejectedJobsHandler): ProcessEngineConfigurationImpl
    {
        $this->customRejectedJobsHandler = $customRejectedJobsHandler;
        return $this;
    }

    public function getEventHandler(?string $eventType): ?EventHandlerInterface
    {
        if (array_key_exists($eventType, $this->eventHandlers)) {
            return $this->eventHandlers[$eventType];
        }
        return null;
    }

    public function setEventHandlers(array $eventHandlers): void
    {
        $this->eventHandlers = $eventHandlers;
    }

    public function getEventHandlers(): array
    {
        return $this->eventHandlers;
    }

    public function getCustomEventHandlers(): array
    {
        return $this->customEventHandlers;
    }

    public function setCustomEventHandlers(array $customEventHandlers): void
    {
        $this->customEventHandlers = $customEventHandlers;
    }

    public function getFailedJobCommandFactory(): FailedJobCommandFactoryInterface
    {
        return $this->failedJobCommandFactory;
    }

    public function setFailedJobCommandFactory(FailedJobCommandFactoryInterface $failedJobCommandFactory): ProcessEngineConfigurationImpl
    {
        $this->failedJobCommandFactory = $failedJobCommandFactory;
        return $this;
    }

    /**
     * Allows configuring a database table prefix which is used for all runtime operations of the process engine.
     * For example, if you specify a prefix named 'PRE1.', activiti will query for executions in a table named
     * 'PRE1.ACT_RU_EXECUTION_'.
     * <p>
     * <p/>
     * <strong>NOTE: the prefix is not respected by automatic database schema management. If you use
     * {@link ProcessEngineConfiguration#DB_SCHEMA_UPDATE_CREATE_DROP}
     * or {@link ProcessEngineConfiguration#DB_SCHEMA_UPDATE_TRUE}, activiti will create the database tables
     * using the default names, regardless of the prefix configured here.</strong>
     */
    public function setDatabaseTablePrefix(?string $databaseTablePrefix): ProcessEngineConfiguration
    {
        $this->databaseTablePrefix = $databaseTablePrefix;
        return $this;
    }

    public function getDatabaseTablePrefix(): ?string
    {
        return $this->databaseTablePrefix;
    }

    public function isCreateDiagramOnDeploy(): bool
    {
        return $this->isCreateDiagramOnDeploy;
    }

    public function setCreateDiagramOnDeploy(bool $createDiagramOnDeploy): ProcessEngineConfiguration
    {
        $this->isCreateDiagramOnDeploy = $createDiagramOnDeploy;
        return $this;
    }

    public function getDatabaseSchema(): ?string
    {
        return $this->databaseSchema;
    }

    public function setDatabaseSchema(?string $databaseSchema): void
    {
        $this->databaseSchema = $databaseSchema;
    }

    public function getIdGeneratorDataSource(): DataSourceInterface
    {
        return $this->idGeneratorDataSource;
    }

    public function setIdGeneratorDataSource(DataSourceInterface $idGeneratorDataSource): void
    {
        $this->idGeneratorDataSource = $idGeneratorDataSource;
    }

    public function getIdGeneratorDataSourceJndiName(): ?string
    {
        return $this->idGeneratorDataSourceJndiName;
    }

    public function setIdGeneratorDataSourceJndiName(?string $idGeneratorDataSourceJndiName): void
    {
        $this->idGeneratorDataSourceJndiName = $idGeneratorDataSourceJndiName;
    }

    public function getProcessApplicationManager(): ProcessApplicationManager
    {
        return $this->processApplicationManager;
    }

    public function setProcessApplicationManager(ProcessApplicationManager $processApplicationManager): void
    {
        $this->processApplicationManager = $processApplicationManager;
    }

    public function getCommandExecutorSchemaOperations(): CommandExecutorInterface
    {
        return $this->commandExecutorSchemaOperations;
    }

    public function setCommandExecutorSchemaOperations(CommandExecutorInterface $commandExecutorSchemaOperations): void
    {
        $this->commandExecutorSchemaOperations = $commandExecutorSchemaOperations;
    }

    public function getCorrelationHandler(): CorrelationHandlerInterface
    {
        return $this->correlationHandler;
    }

    public function setCorrelationHandler(CorrelationHandlerInterface $correlationHandler): void
    {
        $this->correlationHandler = $correlationHandler;
    }

    public function getConditionHandler(): ConditionHandlerInterface
    {
        return $this->conditionHandler;
    }

    public function setConditionHandler(ConditionHandlerInterface $conditionHandler): void
    {
        $this->conditionHandler = $conditionHandler;
    }

    public function setHistoryEventHandler(HistoryEventHandlerInterface $historyEventHandler): ProcessEngineConfigurationImpl
    {
        $this->historyEventHandler = $historyEventHandler;
        return $this;
    }

    public function getHistoryEventHandler(): HistoryEventHandlerInterface
    {
        return $this->historyEventHandler;
    }

    public function isEnableDefaultDbHistoryEventHandler(): bool
    {
        return $this->enableDefaultDbHistoryEventHandler;
    }

    public function setEnableDefaultDbHistoryEventHandler(bool $enableDefaultDbHistoryEventHandler): void
    {
        $this->enableDefaultDbHistoryEventHandler = $enableDefaultDbHistoryEventHandler;
    }

    public function getCustomHistoryEventHandlers(): array
    {
        return $this->customHistoryEventHandlers;
    }

    public function setCustomHistoryEventHandlers(array $customHistoryEventHandlers): void
    {
        $this->customHistoryEventHandlers = $customHistoryEventHandlers;
    }

    public function getIncidentHandler(?string $incidentType): IncidentHandlerInterface
    {
        return $this->incidentHandlers[$incidentType];
    }

    public function addIncidentHandler(IncidentHandlerInterface $incidentHandler): void
    {
        $type = $incidentHandler->getIncidentHandlerType();
        $existsHandler = null;
        if (array_key_exists($type, $this->incidentHandlers)) {
            $existsHandler = $this->incidentHandlers[$type];
        }

        if ($existsHandler instanceof CompositeIncidentHandler) {
            $existsHandler->add($incidentHandler);
        } else {
            $this->incidentHandlers[$incidentHandler->getIncidentHandlerType()] = $incidentHandler;
        }
    }

    public function getIncidentHandlers(): array
    {
        return $this->incidentHandlers;
    }

    public function setIncidentHandlers(array $incidentHandlers): void
    {
        $this->incidentHandlers = $incidentHandlers;
    }

    public function getCustomIncidentHandlers(): array
    {
        return $this->customIncidentHandlers;
    }

    public function setCustomIncidentHandlers(array $customIncidentHandlers): void
    {
        $this->customIncidentHandlers = $customIncidentHandlers;
    }

    public function getBatchHandlers(): array
    {
        return $this->batchHandlers;
    }

    public function setBatchHandlers(array $batchHandlers): void
    {
        $this->batchHandlers = $batchHandlers;
    }

    public function getCustomBatchJobHandlers(): array
    {
        return $this->customBatchJobHandlers;
    }

    public function setCustomBatchJobHandlers(array $customBatchJobHandlers): void
    {
        $this->customBatchJobHandlers = $customBatchJobHandlers;
    }

    public function getBatchJobsPerSeed(): int
    {
        return $this->batchJobsPerSeed;
    }

    public function setBatchJobsPerSeed(int $batchJobsPerSeed): void
    {
        $this->batchJobsPerSeed = $batchJobsPerSeed;
    }

    public function getInvocationsPerBatchJobByBatchType(): array
    {
        return $this->invocationsPerBatchJobByBatchType;
    }

    public function setInvocationsPerBatchJobByBatchType(array $invocationsPerBatchJobByBatchType): ProcessEngineConfigurationImpl
    {
        $this->invocationsPerBatchJobByBatchType = $invocationsPerBatchJobByBatchType;
        return $this;
    }

    public function getInvocationsPerBatchJob(): int
    {
        return $this->invocationsPerBatchJob;
    }

    public function setInvocationsPerBatchJob(int $invocationsPerBatchJob): void
    {
        $this->invocationsPerBatchJob = $invocationsPerBatchJob;
    }

    public function getBatchPollTime(): int
    {
        return $this->batchPollTime;
    }

    public function setBatchPollTime(int $batchPollTime): void
    {
        $this->batchPollTime = $batchPollTime;
    }

    public function getBatchJobPriority(): int
    {
        return $this->batchJobPriority;
    }

    public function setBatchJobPriority(int $batchJobPriority): void
    {
        $this->batchJobPriority = $batchJobPriority;
    }

    public function getHistoryCleanupJobPriority(): int
    {
        return $this->historyCleanupJobPriority;
    }

    public function setHistoryCleanupJobPriority(int $historyCleanupJobPriority): ProcessEngineConfigurationImpl
    {
        $this->historyCleanupJobPriority = $historyCleanupJobPriority;
        return $this;
    }

    public function getIdentityProviderSessionFactory(): SessionFactoryInterface
    {
        return $this->identityProviderSessionFactory;
    }

    public function setIdentityProviderSessionFactory(SessionFactoryInterface $identityProviderSessionFactory): void
    {
        $this->identityProviderSessionFactory = $identityProviderSessionFactory;
    }

    public function getSaltGenerator(): SaltGeneratorInterface
    {
        return $this->saltGenerator;
    }

    public function setSaltGenerator(SaltGeneratorInterface $saltGenerator): void
    {
        $this->saltGenerator = $saltGenerator;
    }

    public function setPasswordEncryptor(PasswordEncryptorInterface $passwordEncryptor): void
    {
        $this->passwordEncryptor = $passwordEncryptor;
    }

    public function getPasswordEncryptor(): PasswordEncryptorInterface
    {
        return $this->passwordEncryptor;
    }

    public function getCustomPasswordChecker(): array
    {
        return $this->customPasswordChecker;
    }

    public function setCustomPasswordChecker(array $customPasswordChecker): void
    {
        $this->customPasswordChecker = $customPasswordChecker;
    }

    public function getPasswordManager(): PasswordManager
    {
        return $this->passwordManager;
    }

    public function setPasswordManager(PasswordManager $passwordManager): void
    {
        $this->passwordManager = $passwordManager;
    }

    public function &getRegisteredDeployments(): array
    {
        return $this->registeredDeployments;
    }

    public function registerDeployment(?string $deploymentId): void
    {
        if (!in_array($deploymentId, $this->registeredDeployments)) {
            $this->registeredDeployments[] = $deploymentId;
        }
    }

    public function setRegisteredDeployments(array $registeredDeployments): void
    {
        $this->registeredDeployments = $registeredDeployments;
    }

    public function getResourceAuthorizationProvider(): ResourceAuthorizationProviderInterface
    {
        return $this->resourceAuthorizationProvider;
    }

    public function setResourceAuthorizationProvider(ResourceAuthorizationProviderInterface $resourceAuthorizationProvider): void
    {
        $this->resourceAuthorizationProvider = $resourceAuthorizationProvider;
    }

    public function getPermissionProvider(): PermissionProviderInterface
    {
        return $this->permissionProvider;
    }

    public function setPermissionProvider(PermissionProviderInterface $permissionProvider): void
    {
        $this->permissionProvider = $permissionProvider;
    }

    public function getProcessEnginePlugins(): array
    {
        return $this->processEnginePlugins;
    }

    public function setProcessEnginePlugins(array $processEnginePlugins): void
    {
        $this->processEnginePlugins = $processEnginePlugins;
    }

    public function setHistoryEventProducer(HistoryEventProducerInterface $historyEventProducer): ProcessEngineConfigurationImpl
    {
        $this->historyEventProducer = $historyEventProducer;
        return $this;
    }

    public function getHistoryEventProducer(): HistoryEventProducerInterface
    {
        return $this->historyEventProducer;
    }

    /*public function setCmmnHistoryEventProducer(CmmnHistoryEventProducer $cmmnHistoryEventProducer): ProcessEngineConfigurationImpl
    {
        $this->cmmnHistoryEventProducer = $cmmnHistoryEventProducer;
        return $this;
    }

    public function getCmmnHistoryEventProducer(): CmmnHistoryEventProducer
    {
        return $this->cmmnHistoryEventProducer;
    }

    public function setDmnHistoryEventProducer(DmnHistoryEventProducer $dmnHistoryEventProducer): ProcessEngineConfigurationImpl
    {
        $this->dmnHistoryEventProducer = $dmnHistoryEventProducer;
        return $this;
    }

    public function getDmnHistoryEventProducer(): DmnHistoryEventProducer
    {
        return $this->dmnHistoryEventProducer;
    }*/

    public function getCustomFormFieldValidators(): array
    {
        return $this->customFormFieldValidators;
    }

    public function setCustomFormFieldValidators(array $customFormFieldValidators): void
    {
        $this->customFormFieldValidators = $customFormFieldValidators;
    }

    public function setFormValidators(FormValidators $formValidators): void
    {
        $this->formValidators = $formValidators;
    }

    public function getFormValidators(): FormValidators
    {
        return $this->formValidators;
    }

    public function setDisableStrictFormParsing(bool $disableStrictFormParsing): ProcessEngineConfigurationImpl
    {
        $this->disableStrictFormParsing = $disableStrictFormParsing;
        return $this;
    }

    public function isDisableStrictFormParsing(): bool
    {
        return $this->disableStrictFormParsing;
    }

    public function isExecutionTreePrefetchEnabled(): bool
    {
        return $this->isExecutionTreePrefetchEnabled;
    }

    public function setExecutionTreePrefetchEnabled(bool $isExecutionTreePrefetchingEnabled): void
    {
        $this->isExecutionTreePrefetchEnabled = $isExecutionTreePrefetchingEnabled;
    }

    public function getProcessEngine(): ProcessEngineImpl
    {
        return $this->processEngine;
    }

    /**
     * If set to true, the process engine will save all script variables (created $from Php Script, Groovy ...)
     * as process variables.
     */
    public function setAutoStoreScriptVariables(bool $autoStoreScriptVariables): void
    {
        $this->autoStoreScriptVariables = $autoStoreScriptVariables;
    }

    /**
     * @return true if the process engine should save all script variables (created $from Php Script, Groovy ...)
     * as process variables.
     */
    public function isAutoStoreScriptVariables(): bool
    {
        return $this->autoStoreScriptVariables;
    }

    /**
     * If set to true, the process engine will attempt to pre-compile script sources at runtime
     * to optimize script task execution performance.
     */
    public function setEnableScriptCompilation(bool $enableScriptCompilation): void
    {
        $this->enableScriptCompilation = $enableScriptCompilation;
    }

    /**
     * @return true if compilation of script sources ins enabled. False otherwise.
     */
    public function isEnableScriptCompilation(): bool
    {
        return $this->enableScriptCompilation;
    }

    public function isEnableGracefulDegradationOnContextSwitchFailure(): bool
    {
        return $this->enableGracefulDegradationOnContextSwitchFailure;
    }

    /**
     * <p>If set to true, the process engine will tolerate certain exceptions that may result
     * from the fact that it cannot switch to the context of a process application that has made
     * a deployment.</p>
     * <p>
     * <p>Affects the following scenarios:</p>
     * <ul>
     * <li><b>Determining job priorities</b>: uses a default priority in case an expression fails to evaluate</li>
     * </ul>
     */
    public function setEnableGracefulDegradationOnContextSwitchFailure(bool $enableGracefulDegradationOnContextSwitchFailure): void
    {
        $this->enableGracefulDegradationOnContextSwitchFailure = $enableGracefulDegradationOnContextSwitchFailure;
    }

    /**
     * @return true if the process engine acquires an exclusive lock when creating a deployment.
     */
    public function isDeploymentLockUsed(): bool
    {
        return $this->isDeploymentLockUsed;
    }

    /**
     * If set to true, the process engine will acquire an exclusive lock when creating a deployment.
     * This ensures that {@link DeploymentBuilder#enableDuplicateFiltering()} works correctly in a clustered environment.
     */
    public function setDeploymentLockUsed(bool $isDeploymentLockUsed): void
    {
        $this->isDeploymentLockUsed = $isDeploymentLockUsed;
    }

    /**
     * @return true if deployment processing must be synchronized
     */
    public function isDeploymentSynchronized(): bool
    {
        return $this->isDeploymentSynchronized;
    }

    /**
     * Sets if deployment processing must be synchronized.
     * @param deploymentSynchronized {@code true} when deployment must be synchronized,
     * {@code false} when several depoloyments may be processed in parallel
     */
    public function setDeploymentSynchronized(bool $deploymentSynchronized): void
    {
        $this->isDeploymentSynchronized = $deploymentSynchronized;
    }

    public function isCmmnEnabled(): bool
    {
        return $this->cmmnEnabled;
    }

    /*public function setCmmnEnabled(bool $cmmnEnabled): void
    {
        $this->cmmnEnabled = $cmmnEnabled;
    }*/

    public function isDmnEnabled(): bool
    {
        return $this->dmnEnabled;
    }

    /*public function setDmnEnabled(bool $dmnEnabled): void
    {
        $this->dmnEnabled = $dmnEnabled;
    }*/

    public function isStandaloneTasksEnabled(): bool
    {
        return $this->standaloneTasksEnabled;
    }

    public function setStandaloneTasksEnabled(bool $standaloneTasksEnabled): ProcessEngineConfigurationImpl
    {
        $this->standaloneTasksEnabled = $standaloneTasksEnabled;
        return $this;
    }

    public function isCompositeIncidentHandlersEnabled(): bool
    {
        return $this->isCompositeIncidentHandlersEnabled;
    }

    public function setCompositeIncidentHandlersEnabled(bool $compositeIncidentHandlersEnabled): ProcessEngineConfigurationImpl
    {
        $this->isCompositeIncidentHandlersEnabled = $compositeIncidentHandlersEnabled;
        return $this;
    }

    public function getScriptFactory(): ScriptFactory
    {
        return $this->scriptFactory;
    }

    public function getScriptingEnvironment(): ScriptingEnvironment
    {
        return $this->scriptingEnvironment;
    }

    public function setScriptFactory(ScriptFactory $scriptFactory): void
    {
        $this->scriptFactory = $scriptFactory;
    }

    public function getScriptEngineResolver(): ScriptEngineResolverInterface
    {
        return $this->scriptEngineResolver;
    }

    public function setScriptEngineResolver(ScriptEngineResolverInterface $scriptEngineResolver): ProcessEngineConfigurationImpl
    {
        $this->scriptEngineResolver = $scriptEngineResolver;
        if ($this->scriptingEngines !== null) {
            $this->scriptingEngines->setScriptEngineResolver($scriptEngineResolver);
        }
        return $this;
    }

    public function setScriptingEnvironment(ScriptingEnvironment $scriptingEnvironment): void
    {
        $this->scriptingEnvironment = $scriptingEnvironment;
    }

    public function getEnvScriptResolvers(): array
    {
        return $this->scriptEnvResolvers;
    }

    public function setEnvScriptResolvers(array $scriptEnvResolvers): void
    {
        $this->scriptEnvResolvers = $scriptEnvResolvers;
    }

    public function getScriptEngineNameJavaScript(): ?string
    {
        return $this->scriptEngineNameJavaScript;
    }

    public function setScriptEngineNameJavaScript(?string $scriptEngineNameJavaScript): ProcessEngineConfigurationImpl
    {
        $this->scriptEngineNameJavaScript = $scriptEngineNameJavaScript;
        return $this;
    }

    public function setArtifactFactory(ArtifactFactoryInterface $artifactFactory): ProcessEngineConfigurationImpl
    {
        $this->artifactFactory = $artifactFactory;
        return $this;
    }

    public function getArtifactFactory(): ArtifactFactoryInterface
    {
        return $this->artifactFactory;
    }

    public function getDefaultSerializationFormat(): ?string
    {
        return $this->defaultSerializationFormat;
    }

    public function setDefaultSerializationFormat(?string $defaultSerializationFormat): ProcessEngineConfigurationImpl
    {
        $this->defaultSerializationFormat = $defaultSerializationFormat;
        return $this;
    }

    public function isPhpSerializationFormatEnabled(): bool
    {
        return $this->phpSerializationFormatEnabled;
    }

    public function setJavaSerializationFormatEnabled(bool $phpSerializationFormatEnabled): void
    {
        $this->phpSerializationFormatEnabled = $phpSerializationFormatEnabled;
    }

    public function setDefaultCharsetName(?string $defaultCharsetName): ProcessEngineConfigurationImpl
    {
        $this->defaultCharsetName = $defaultCharsetName;
        return $this;
    }

    public function setDefaultCharset($defautlCharset): ProcessEngineConfigurationImpl
    {
        $this->defaultCharset = $defautlCharset;
        return $this;
    }

    public function getDefaultCharset(): ?string
    {
        return $this->defaultCharset;
    }

    public function isDbEntityCacheReuseEnabled(): bool
    {
        return $this->isDbEntityCacheReuseEnabled;
    }

    public function setDbEntityCacheReuseEnabled(bool $isDbEntityCacheReuseEnabled): ProcessEngineConfigurationImpl
    {
        $this->isDbEntityCacheReuseEnabled = $isDbEntityCacheReuseEnabled;
        return $this;
    }

    public function getDbEntityCacheKeyMapping(): DbEntityCacheKeyMapping
    {
        return $this->dbEntityCacheKeyMapping;
    }

    public function setDbEntityCacheKeyMapping(DbEntityCacheKeyMapping $dbEntityCacheKeyMapping): ProcessEngineConfigurationImpl
    {
        $this->dbEntityCacheKeyMapping = $dbEntityCacheKeyMapping;
        return $this;
    }

    public function setCustomHistoryLevels(array $customHistoryLevels): ProcessEngineConfigurationImpl
    {
        $this->customHistoryLevels = $customHistoryLevels;
        return $this;
    }

    public function getHistoryLevels(): array
    {
        return $this->historyLevels;
    }

    public function getCustomHistoryLevels(): array
    {
        return $this->customHistoryLevels;
    }

    public function isInvokeCustomVariableListeners(): bool
    {
        return $this->isInvokeCustomVariableListeners;
    }

    public function setInvokeCustomVariableListeners(bool $isInvokeCustomVariableListeners): ProcessEngineConfigurationImpl
    {
        $this->isInvokeCustomVariableListeners = $isInvokeCustomVariableListeners;
        return $this;
    }

    public function close(): void
    {
    }

    public function getMetricsRegistry(): MetricsRegistry
    {
        return $this->metricsRegistry;
    }

    public function setMetricsRegistry(MetricsRegistry $metricsRegistry): ProcessEngineConfigurationImpl
    {
        $this->metricsRegistry = $metricsRegistry;
        return $this;
    }

    public function setMetricsEnabled(bool $isMetricsEnabled): ProcessEngineConfigurationImpl
    {
        $this->isMetricsEnabled = $isMetricsEnabled;
        return $this;
    }

    public function isMetricsEnabled(): bool
    {
        return $this->isMetricsEnabled;
    }

    public function getDbMetricsReporter(): DbMetricsReporter
    {
        return $this->dbMetricsReporter;
    }

    public function setDbMetricsReporter(DbMetricsReporter $dbMetricsReporter): ProcessEngineConfigurationImpl
    {
        $this->dbMetricsReporter = $dbMetricsReporter;
        return $this;
    }

    public function isDbMetricsReporterActivate(): bool
    {
        return $this->isDbMetricsReporterActivate;
    }

    public function setDbMetricsReporterActivate(bool $isDbMetricsReporterEnabled): ProcessEngineConfigurationImpl
    {
        $this->isDbMetricsReporterActivate = $isDbMetricsReporterEnabled;
        return $this;
    }

    public function getMetricsReporterIdProvider(): ?MetricsReporterIdProviderInterface
    {
        return $this->metricsReporterIdProvider;
    }

    public function setMetricsReporterIdProvider(MetricsReporterIdProviderInterface $metricsReporterIdProvider): ProcessEngineConfigurationImpl
    {
        $this->metricsReporterIdProvider = $metricsReporterIdProvider;
        return $this;
    }

    public function getHostname(): ?string
    {
        return $this->hostname;
    }

    public function setHostname(?string $hostname): ProcessEngineConfigurationImpl
    {
        $this->hostname = $hostname;
        return $this;
    }

    public function getHostnameProvider(): HostnameProvider
    {
        return $this->hostnameProvider;
    }

    public function setHostnameProvider(HostnameProvider $hostnameProvider): ProcessEngineConfigurationImpl
    {
        $this->hostnameProvider = $hostnameProvider;
        return $this;
    }

    public function isTaskMetricsEnabled(): bool
    {
        return $this->isTaskMetricsEnabled;
    }

    public function setTaskMetricsEnabled(bool $isTaskMetricsEnabled): ProcessEngineConfigurationImpl
    {
        $this->isTaskMetricsEnabled = $isTaskMetricsEnabled;
        return $this;
    }

    public function isEnableScriptEngineCaching(): bool
    {
        return $this->enableScriptEngineCaching;
    }

    public function setEnableScriptEngineCaching(bool $enableScriptEngineCaching): ProcessEngineConfigurationImpl
    {
        $this->enableScriptEngineCaching = $enableScriptEngineCaching;
        return $this;
    }

    public function isEnableFetchScriptEngineFromProcessApplication(): bool
    {
        return $this->enableFetchScriptEngineFromProcessApplication;
    }

    public function setEnableFetchScriptEngineFromProcessApplication(bool $enable): ProcessEngineConfigurationImpl
    {
        $this->enableFetchScriptEngineFromProcessApplication = $enable;
        return $this;
    }

    public function isEnableScriptEngineLoadExternalResources(): bool
    {
        return $this->enableScriptEngineLoadExternalResources;
    }

    public function setEnableScriptEngineLoadExternalResources(bool $enableScriptEngineLoadExternalResources): ProcessEngineConfigurationImpl
    {
        $this->enableScriptEngineLoadExternalResources = $enableScriptEngineLoadExternalResources;
        return $this;
    }

    public function isEnableScriptEngineNashornCompatibility(): bool
    {
        return $this->enableScriptEngineNashornCompatibility;
    }

    public function setEnableScriptEngineNashornCompatibility(bool $enableScriptEngineNashornCompatibility): ProcessEngineConfigurationImpl
    {
        $this->enableScriptEngineNashornCompatibility = $enableScriptEngineNashornCompatibility;
        return $this;
    }

    public function isConfigureScriptEngineHostAccess(): bool
    {
        return $this->configureScriptEngineHostAccess;
    }

    public function setConfigureScriptEngineHostAccess(bool $configureScriptEngineHostAccess): ProcessEngineConfigurationImpl
    {
        $this->configureScriptEngineHostAccess = $configureScriptEngineHostAccess;
        return $this;
    }

    public function isEnableExpressionsInAdhocQueries(): bool
    {
        return $this->enableExpressionsInAdhocQueries;
    }

    public function setEnableExpressionsInAdhocQueries(bool $enableExpressionsInAdhocQueries): void
    {
        $this->enableExpressionsInAdhocQueries = $enableExpressionsInAdhocQueries;
    }

    public function isEnableExpressionsInStoredQueries(): bool
    {
        return $this->enableExpressionsInStoredQueries;
    }

    public function setEnableExpressionsInStoredQueries(bool $enableExpressionsInStoredQueries): void
    {
        $this->enableExpressionsInStoredQueries = $enableExpressionsInStoredQueries;
    }

    public function isEnableXxeProcessing(): bool
    {
        return $this->enableXxeProcessing;
    }

    public function setEnableXxeProcessing(bool $enableXxeProcessing): void
    {
        $this->enableXxeProcessing = $enableXxeProcessing;
    }

    public function setBpmnStacktraceVerbose(bool $isBpmnStacktraceVerbose): ProcessEngineConfigurationImpl
    {
        $this->isBpmnStacktraceVerbose = $isBpmnStacktraceVerbose;
        return $this;
    }

    public function isBpmnStacktraceVerbose(): bool
    {
        return $this->isBpmnStacktraceVerbose;
    }

    public function isForceCloseMybatisConnectionPool(): bool
    {
        return $this->forceCloseMybatisConnectionPool;
    }

    public function setForceCloseMybatisConnectionPool(bool $forceCloseMybatisConnectionPool): ProcessEngineConfigurationImpl
    {
        $this->forceCloseMybatisConnectionPool = $forceCloseMybatisConnectionPool;
        return $this;
    }

    public function isRestrictUserOperationLogToAuthenticatedUsers(): bool
    {
        return $this->restrictUserOperationLogToAuthenticatedUsers;
    }

    public function setRestrictUserOperationLogToAuthenticatedUsers(bool $restrictUserOperationLogToAuthenticatedUsers): ProcessEngineConfigurationImpl
    {
        $this->restrictUserOperationLogToAuthenticatedUsers = $restrictUserOperationLogToAuthenticatedUsers;
        return $this;
    }

    public function setTenantIdProvider(?TenantIdProvider $tenantIdProvider): ProcessEngineConfigurationImpl
    {
        $this->tenantIdProvider = $tenantIdProvider;
        return $this;
    }

    public function getTenantIdProvider(): ?TenantIdProvider
    {
        return $this->tenantIdProvider;
    }

    /*public function setMigrationActivityMatcher(MigrationActivityMatcher $migrationActivityMatcher): void
    {
        $this->migrationActivityMatcher = $migrationActivityMatcher;
    }

    public function getMigrationActivityMatcher(): MigrationActivityMatcher
    {
        return $this->migrationActivityMatcher;
    }

    public function setCustomPreMigrationActivityValidators(array $customPreMigrationActivityValidators): void
    {
        $this->customPreMigrationActivityValidators = $customPreMigrationActivityValidators;
    }

    public function getCustomPreMigrationActivityValidators(): array
    {
        return customPreMigrationActivityValidators;
    }

    public function setCustomPostMigrationActivityValidators(array $customPostMigrationActivityValidators): void
    {
        $this->customPostMigrationActivityValidators = $customPostMigrationActivityValidators;
    }

    public function getCustomPostMigrationActivityValidators(): array
    {
        return customPostMigrationActivityValidators;
    }

    public function getDefaultMigrationActivityValidators(): array
    {
        List<MigrationActivityValidator> migrationActivityValidators = new ArrayList<>();
        migrationActivityValidators->add(SupportedActivityValidator.INSTANCE);
        migrationActivityValidators->add(SupportedPassiveEventTriggerActivityValidator.INSTANCE);
        migrationActivityValidators->add(NoCompensationHandlerActivityValidator.INSTANCE);
        return migrationActivityValidators;
    }

    public function setMigrationInstructionGenerator(MigrationInstructionGenerator $migrationInstructionGenerator): void
    {
        $this->migrationInstructionGenerator = $migrationInstructionGenerator;
    }

    public function getMigrationInstructionGenerator(): MigrationInstructionGenerator
    {
        return $this->migrationInstructionGenerator;
    }

    public function setMigrationInstructionValidators(array $migrationInstructionValidators): void
    {
        $this->migrationInstructionValidators = $migrationInstructionValidators;
    }

    public function getMigrationInstructionValidators(): array
    {
        return migrationInstructionValidators;
    }

    public function setCustomPostMigrationInstructionValidators(array $customPostMigrationInstructionValidators): void
    {
        $this->customPostMigrationInstructionValidators = $customPostMigrationInstructionValidators;
    }

    public function getCustomPostMigrationInstructionValidators(): array
    {
        return customPostMigrationInstructionValidators;
    }

    public function setCustomPreMigrationInstructionValidators(array $customPreMigrationInstructionValidators): void
    {
        $this->customPreMigrationInstructionValidators = $customPreMigrationInstructionValidators;
    }

    public function getCustomPreMigrationInstructionValidators(): array
    {
        return customPreMigrationInstructionValidators;
    }

    public function getDefaultMigrationInstructionValidators(): array
    {
        List<MigrationInstructionValidator> migrationInstructionValidators = new ArrayList<>();
        migrationInstructionValidators->add(new SameBehaviorInstructionValidator());
        migrationInstructionValidators->add(new SameEventTypeValidator());
        migrationInstructionValidators->add(new OnlyOnceMappedActivityInstructionValidator());
        migrationInstructionValidators->add(new CannotAddMultiInstanceBodyValidator());
        migrationInstructionValidators->add(new CannotAddMultiInstanceInnerActivityValidator());
        migrationInstructionValidators->add(new CannotRemoveMultiInstanceInnerActivityValidator());
        migrationInstructionValidators->add(new GatewayMappingValidator());
        migrationInstructionValidators->add(new SameEventScopeInstructionValidator());
        migrationInstructionValidators->add(new UpdateEventTriggersValidator());
        migrationInstructionValidators->add(new AdditionalFlowScopeInstructionValidator());
        migrationInstructionValidators->add(new ConditionalEventUpdateEventTriggerValidator());
        return migrationInstructionValidators;
    }

    public function setMigratingActivityInstanceValidators(array $migratingActivityInstanceValidators): void
    {
        $this->migratingActivityInstanceValidators = $migratingActivityInstanceValidators;
    }

    public function getMigratingActivityInstanceValidators(): array
    {
        return migratingActivityInstanceValidators;
    }

    public function setCustomPostMigratingActivityInstanceValidators(array $customPostMigratingActivityInstanceValidators): void
    {
        $this->customPostMigratingActivityInstanceValidators = $customPostMigratingActivityInstanceValidators;
    }

    public function getCustomPostMigratingActivityInstanceValidators(): array
    {
        return customPostMigratingActivityInstanceValidators;
    }

    public function setCustomPreMigratingActivityInstanceValidators(array $customPreMigratingActivityInstanceValidators): void
    {
        $this->customPreMigratingActivityInstanceValidators = $customPreMigratingActivityInstanceValidators;
    }

    public function getCustomPreMigratingActivityInstanceValidators(): array
    {
        return customPreMigratingActivityInstanceValidators;
    }

    public function getMigratingTransitionInstanceValidators(): array
    {
        return migratingTransitionInstanceValidators;
    }

    public function getMigratingCompensationInstanceValidators(): array
    {
        return migratingCompensationInstanceValidators;
    }

    public function getDefaultMigratingActivityInstanceValidators(): array
    {
        List<MigratingActivityInstanceValidator> migratingActivityInstanceValidators = new ArrayList<>();

        migratingActivityInstanceValidators->add(new NoUnmappedLeafInstanceValidator());
        migratingActivityInstanceValidators->add(new VariableConflictActivityInstanceValidator());
        migratingActivityInstanceValidators->add(new SupportedActivityInstanceValidator());

        return migratingActivityInstanceValidators;
    }

    public function getDefaultMigratingTransitionInstanceValidators(): array
    {
        List<MigratingTransitionInstanceValidator> migratingTransitionInstanceValidators = new ArrayList<>();

        migratingTransitionInstanceValidators->add(new NoUnmappedLeafInstanceValidator());
        migratingTransitionInstanceValidators->add(new AsyncAfterMigrationValidator());
        migratingTransitionInstanceValidators->add(new AsyncProcessStartMigrationValidator());
        migratingTransitionInstanceValidators->add(new AsyncMigrationValidator());

        return migratingTransitionInstanceValidators;
    }*/

    public function getCommandCheckers(): array
    {
        return $this->commandCheckers;
    }

    public function setCommandCheckers(array $commandCheckers): void
    {
        $this->commandCheckers = $commandCheckers;
    }

    public function setUseSharedSqlSessionFactory(bool $isUseSharedSqlSessionFactory): ProcessEngineConfigurationImpl
    {
        $this->isUseSharedSqlSessionFactory = $isUseSharedSqlSessionFactory;
        return $this;
    }

    public function isUseSharedSqlSessionFactory(): bool
    {
        return $this->isUseSharedSqlSessionFactory;
    }

    public function getDisableStrictCallActivityValidation(): bool
    {
        return $this->disableStrictCallActivityValidation;
    }

    public function setDisableStrictCallActivityValidation(bool $disableStrictCallActivityValidation): void
    {
        $this->disableStrictCallActivityValidation = $disableStrictCallActivityValidation;
    }

    public function getHistoryCleanupBatchWindowStartTime(): ?string
    {
        return $this->historyCleanupBatchWindowStartTime;
    }

    public function setHistoryCleanupBatchWindowStartTime(?string $historyCleanupBatchWindowStartTime): void
    {
        $this->historyCleanupBatchWindowStartTime = $historyCleanupBatchWindowStartTime;
    }

    public function getHistoryCleanupBatchWindowEndTime(): ?string
    {
        return $this->historyCleanupBatchWindowEndTime;
    }

    public function setHistoryCleanupBatchWindowEndTime(?string $historyCleanupBatchWindowEndTime): void
    {
        $this->historyCleanupBatchWindowEndTime = $historyCleanupBatchWindowEndTime;
    }

    public function getMondayHistoryCleanupBatchWindowStartTime(): ?string
    {
        return $this->mondayHistoryCleanupBatchWindowStartTime;
    }

    public function setMondayHistoryCleanupBatchWindowStartTime(?string $mondayHistoryCleanupBatchWindowStartTime): void
    {
        $this->mondayHistoryCleanupBatchWindowStartTime = $mondayHistoryCleanupBatchWindowStartTime;
    }

    public function getMondayHistoryCleanupBatchWindowEndTime(): ?string
    {
        return $this->mondayHistoryCleanupBatchWindowEndTime;
    }

    public function setMondayHistoryCleanupBatchWindowEndTime(?string $mondayHistoryCleanupBatchWindowEndTime): void
    {
        $this->mondayHistoryCleanupBatchWindowEndTime = $mondayHistoryCleanupBatchWindowEndTime;
    }

    public function getTuesdayHistoryCleanupBatchWindowStartTime(): ?string
    {
        return $this->tuesdayHistoryCleanupBatchWindowStartTime;
    }

    public function setTuesdayHistoryCleanupBatchWindowStartTime(?string $tuesdayHistoryCleanupBatchWindowStartTime): void
    {
        $this->tuesdayHistoryCleanupBatchWindowStartTime = $tuesdayHistoryCleanupBatchWindowStartTime;
    }

    public function getTuesdayHistoryCleanupBatchWindowEndTime(): ?string
    {
        return $this->tuesdayHistoryCleanupBatchWindowEndTime;
    }

    public function setTuesdayHistoryCleanupBatchWindowEndTime(?string $tuesdayHistoryCleanupBatchWindowEndTime): void
    {
        $this->tuesdayHistoryCleanupBatchWindowEndTime = $tuesdayHistoryCleanupBatchWindowEndTime;
    }

    public function getWednesdayHistoryCleanupBatchWindowStartTime(): ?string
    {
        return $this->wednesdayHistoryCleanupBatchWindowStartTime;
    }

    public function setWednesdayHistoryCleanupBatchWindowStartTime(?string $wednesdayHistoryCleanupBatchWindowStartTime): void
    {
        $this->wednesdayHistoryCleanupBatchWindowStartTime = $wednesdayHistoryCleanupBatchWindowStartTime;
    }

    public function getWednesdayHistoryCleanupBatchWindowEndTime(): ?string
    {
        return $this->wednesdayHistoryCleanupBatchWindowEndTime;
    }

    public function setWednesdayHistoryCleanupBatchWindowEndTime(?string $wednesdayHistoryCleanupBatchWindowEndTime): void
    {
        $this->wednesdayHistoryCleanupBatchWindowEndTime = $wednesdayHistoryCleanupBatchWindowEndTime;
    }

    public function getThursdayHistoryCleanupBatchWindowStartTime(): ?string
    {
        return $this->thursdayHistoryCleanupBatchWindowStartTime;
    }

    public function setThursdayHistoryCleanupBatchWindowStartTime(?string $thursdayHistoryCleanupBatchWindowStartTime): void
    {
        $this->thursdayHistoryCleanupBatchWindowStartTime = $thursdayHistoryCleanupBatchWindowStartTime;
    }

    public function getThursdayHistoryCleanupBatchWindowEndTime(): ?string
    {
        return $this->thursdayHistoryCleanupBatchWindowEndTime;
    }

    public function setThursdayHistoryCleanupBatchWindowEndTime(?string $thursdayHistoryCleanupBatchWindowEndTime): void
    {
        $this->thursdayHistoryCleanupBatchWindowEndTime = $thursdayHistoryCleanupBatchWindowEndTime;
    }

    public function getFridayHistoryCleanupBatchWindowStartTime(): ?string
    {
        return $this->fridayHistoryCleanupBatchWindowStartTime;
    }

    public function setFridayHistoryCleanupBatchWindowStartTime(?string $fridayHistoryCleanupBatchWindowStartTime): void
    {
        $this->fridayHistoryCleanupBatchWindowStartTime = $fridayHistoryCleanupBatchWindowStartTime;
    }

    public function getFridayHistoryCleanupBatchWindowEndTime(): ?string
    {
        return $this->fridayHistoryCleanupBatchWindowEndTime;
    }

    public function setFridayHistoryCleanupBatchWindowEndTime(?string $fridayHistoryCleanupBatchWindowEndTime): void
    {
        $this->fridayHistoryCleanupBatchWindowEndTime = $fridayHistoryCleanupBatchWindowEndTime;
    }

    public function getSaturdayHistoryCleanupBatchWindowStartTime(): ?string
    {
        return $this->saturdayHistoryCleanupBatchWindowStartTime;
    }

    public function setSaturdayHistoryCleanupBatchWindowStartTime(?string $saturdayHistoryCleanupBatchWindowStartTime): void
    {
        $this->saturdayHistoryCleanupBatchWindowStartTime = $saturdayHistoryCleanupBatchWindowStartTime;
    }

    public function getSaturdayHistoryCleanupBatchWindowEndTime(): ?string
    {
        return $this->saturdayHistoryCleanupBatchWindowEndTime;
    }

    public function setSaturdayHistoryCleanupBatchWindowEndTime(?string $saturdayHistoryCleanupBatchWindowEndTime): void
    {
        $this->saturdayHistoryCleanupBatchWindowEndTime = $saturdayHistoryCleanupBatchWindowEndTime;
    }

    public function getSundayHistoryCleanupBatchWindowStartTime(): ?string
    {
        return $this->sundayHistoryCleanupBatchWindowStartTime;
    }

    public function setSundayHistoryCleanupBatchWindowStartTime(?string $sundayHistoryCleanupBatchWindowStartTime): void
    {
        $this->sundayHistoryCleanupBatchWindowStartTime = $sundayHistoryCleanupBatchWindowStartTime;
    }

    public function getSundayHistoryCleanupBatchWindowEndTime(): ?string
    {
        return $this->sundayHistoryCleanupBatchWindowEndTime;
    }

    public function setSundayHistoryCleanupBatchWindowEndTime(?string $sundayHistoryCleanupBatchWindowEndTime): void
    {
        $this->sundayHistoryCleanupBatchWindowEndTime = $sundayHistoryCleanupBatchWindowEndTime;
    }

    public function getHistoryCleanupBatchWindowStartTimeAsDate(): Date
    {
        return $this->historyCleanupBatchWindowStartTimeAsDate;
    }

    public function setHistoryCleanupBatchWindowStartTimeAsDate(?string $historyCleanupBatchWindowStartTimeAsDate): void
    {
        $this->historyCleanupBatchWindowStartTimeAsDate = $historyCleanupBatchWindowStartTimeAsDate;
    }

    public function setHistoryCleanupBatchWindowEndTimeAsDate(?string $historyCleanupBatchWindowEndTimeAsDate): void
    {
        $this->historyCleanupBatchWindowEndTimeAsDate = $historyCleanupBatchWindowEndTimeAsDate;
    }

    public function getHistoryCleanupBatchWindowEndTimeAsDate(): Date
    {
        return $this->historyCleanupBatchWindowEndTimeAsDate;
    }

    public function getHistoryCleanupBatchWindows(): array
    {
        return $this->historyCleanupBatchWindows;
    }

    public function setHistoryCleanupBatchWindows(array $historyCleanupBatchWindows): void
    {
        $this->historyCleanupBatchWindows = $historyCleanupBatchWindows;
    }

    public function getHistoryCleanupBatchSize(): int
    {
        return $this->historyCleanupBatchSize;
    }

    public function setHistoryCleanupBatchSize(int $historyCleanupBatchSize): void
    {
        $this->historyCleanupBatchSize = $historyCleanupBatchSize;
    }

    public function getHistoryCleanupBatchThreshold(): int
    {
        return $this->historyCleanupBatchThreshold;
    }

    public function setHistoryCleanupBatchThreshold(int $historyCleanupBatchThreshold): void
    {
        $this->historyCleanupBatchThreshold = $historyCleanupBatchThreshold;
    }

    public function isHistoryCleanupMetricsEnabled(): bool
    {
        return $this->historyCleanupMetricsEnabled;
    }

    public function setHistoryCleanupMetricsEnabled(bool $historyCleanupMetricsEnabled): void
    {
        $this->historyCleanupMetricsEnabled = $historyCleanupMetricsEnabled;
    }

    public function isHistoryCleanupEnabled(): bool
    {
        return $this->historyCleanupEnabled;
    }

    public function setHistoryCleanupEnabled(bool $historyCleanupEnabled): ProcessEngineConfigurationImpl
    {
        $this->historyCleanupEnabled = $historyCleanupEnabled;
        return $this;
    }

    public function getHistoryTimeToLive(): ?string
    {
        return $this->historyTimeToLive;
    }

    public function setHistoryTimeToLive(?string $historyTimeToLive): void
    {
        $this->historyTimeToLive = $historyTimeToLive;
    }

    public function getBatchOperationHistoryTimeToLive(): ?string
    {
        return $this->batchOperationHistoryTimeToLive;
    }

    public function getHistoryCleanupDegreeOfParallelism(): int
    {
        return $this->historyCleanupDegreeOfParallelism;
    }

    public function setHistoryCleanupDegreeOfParallelism(int $historyCleanupDegreeOfParallelism): void
    {
        $this->historyCleanupDegreeOfParallelism = $historyCleanupDegreeOfParallelism;
    }

    public function setBatchOperationHistoryTimeToLive(?string $batchOperationHistoryTimeToLive): void
    {
        $this->batchOperationHistoryTimeToLive = $batchOperationHistoryTimeToLive;
    }

    public function getBatchOperationsForHistoryCleanup(): array
    {
        return $this->batchOperationsForHistoryCleanup;
    }

    public function setBatchOperationsForHistoryCleanup(array $batchOperationsForHistoryCleanup): void
    {
        $this->batchOperationsForHistoryCleanup = $batchOperationsForHistoryCleanup;
    }

    public function getParsedBatchOperationsForHistoryCleanup(): array
    {
        return $this->parsedBatchOperationsForHistoryCleanup;
    }

    public function setParsedBatchOperationsForHistoryCleanup(array $parsedBatchOperationsForHistoryCleanup): void
    {
        $this->parsedBatchOperationsForHistoryCleanup = $parsedBatchOperationsForHistoryCleanup;
    }

    public function getHistoryCleanupJobLogTimeToLive(): ?string
    {
        return $this->historyCleanupJobLogTimeToLive;
    }

    public function setHistoryCleanupJobLogTimeToLive(?string $historyCleanupJobLogTimeToLive): ProcessEngineConfigurationImpl
    {
        $this->historyCleanupJobLogTimeToLive = $historyCleanupJobLogTimeToLive;
        return $this;
    }

    public function getTaskMetricsTimeToLive(): ?string
    {
        return $this->taskMetricsTimeToLive;
    }

    public function setTaskMetricsTimeToLive(?string $taskMetricsTimeToLive): ProcessEngineConfigurationImpl
    {
        $this->taskMetricsTimeToLive = $taskMetricsTimeToLive;
        return $this;
    }

    public function getParsedTaskMetricsTimeToLive(): Integer
    {
        return $this->parsedTaskMetricsTimeToLive;
    }

    public function setParsedTaskMetricsTimeToLive(Integer $parsedTaskMetricsTimeToLive): ProcessEngineConfigurationImpl
    {
        $this->parsedTaskMetricsTimeToLive = $parsedTaskMetricsTimeToLive;
        return $this;
    }

    public function getBatchWindowManager(): BatchWindowManager
    {
        return $this->batchWindowManager;
    }

    public function setBatchWindowManager(BatchWindowManager $batchWindowManager): void
    {
        $this->batchWindowManager = $batchWindowManager;
    }

    public function getHistoryRemovalTimeProvider(): HistoryRemovalTimeProviderInterface
    {
        return $this->historyRemovalTimeProvider;
    }

    public function setHistoryRemovalTimeProvider(HistoryRemovalTimeProviderInterface $removalTimeProvider): ProcessEngineConfigurationImpl
    {
        $this->historyRemovalTimeProvider = $removalTimeProvider;
        return $this;
    }

    public function getHistoryRemovalTimeStrategy(): ?string
    {
        return $this->historyRemovalTimeStrategy;
    }

    public function setHistoryRemovalTimeStrategy(?string $removalTimeStrategy): ProcessEngineConfigurationImpl
    {
        $this->historyRemovalTimeStrategy = $removalTimeStrategy;
        return $this;
    }

    public function getHistoryCleanupStrategy(): ?string
    {
        return $this->historyCleanupStrategy;
    }

    public function setHistoryCleanupStrategy(?string $historyCleanupStrategy): ProcessEngineConfigurationImpl
    {
        $this->historyCleanupStrategy = $historyCleanupStrategy;
        return $this;
    }

    public function getFailedJobListenerMaxRetries(): int
    {
        return $this->failedJobListenerMaxRetries;
    }

    public function setFailedJobListenerMaxRetries(int $failedJobListenerMaxRetries): void
    {
        $this->failedJobListenerMaxRetries = $failedJobListenerMaxRetries;
    }

    public function getFailedJobRetryTimeCycle(): ?string
    {
        return $this->failedJobRetryTimeCycle;
    }

    public function setFailedJobRetryTimeCycle(?string $failedJobRetryTimeCycle): void
    {
        $this->failedJobRetryTimeCycle = $failedJobRetryTimeCycle;
    }

    public function getLoginMaxAttempts(): int
    {
        return $this->loginMaxAttempts;
    }

    public function setLoginMaxAttempts(int $loginMaxAttempts): void
    {
        $this->loginMaxAttempts = $loginMaxAttempts;
    }

    public function getLoginDelayFactor(): int
    {
        return $this->loginDelayFactor;
    }

    public function setLoginDelayFactor(int $loginDelayFactor): void
    {
        $this->loginDelayFactor = $loginDelayFactor;
    }

    public function getLoginDelayMaxTime(): int
    {
        return $this->loginDelayMaxTime;
    }

    public function setLoginDelayMaxTime(int $loginDelayMaxTime): void
    {
        $this->loginDelayMaxTime = $loginDelayMaxTime;
    }

    public function getLoginDelayBase(): int
    {
        return $this->loginDelayBase;
    }

    public function setLoginDelayBase(int $loginInitialDelay): void
    {
        $this->loginDelayBase = $loginInitialDelay;
    }

    public function getAdminGroups(): array
    {
        return $this->adminGroups;
    }

    public function setAdminGroups(array $adminGroups): void
    {
        $this->adminGroups = $adminGroups;
    }

    public function getAdminUsers(): array
    {
        return $this->adminUsers;
    }

    public function setAdminUsers(array $adminUsers): void
    {
        $this->adminUsers = $adminUsers;
    }

    public function getQueryMaxResultsLimit(): int
    {
        return $this->queryMaxResultsLimit;
    }

    public function setQueryMaxResultsLimit(int $queryMaxResultsLimit): ProcessEngineConfigurationImpl
    {
        $this->queryMaxResultsLimit = $queryMaxResultsLimit;
        return $this;
    }

    public function getLoggingContextActivityId(): ?string
    {
        return $this->loggingContextActivityId;
    }

    public function setLoggingContextActivityId(?string $loggingContextActivityId): ProcessEngineConfigurationImpl
    {
        $this->loggingContextActivityId = $loggingContextActivityId;
        return $this;
    }

    public function getLoggingContextActivityName(): ?string
    {
        return $this->loggingContextActivityName;
    }

    public function setLoggingContextActivityName(?string $loggingContextActivityName): ProcessEngineConfigurationImpl
    {
        $this->loggingContextActivityName = $loggingContextActivityName;
        return $this;
    }

    public function getLoggingContextApplicationName(): ?string
    {
        return $this->loggingContextApplicationName;
    }

    public function setLoggingContextApplicationName(?string $loggingContextApplicationName): ProcessEngineConfigurationImpl
    {
        $this->loggingContextApplicationName = $loggingContextApplicationName;
        return $this;
    }

    public function getLoggingContextBusinessKey(): ?string
    {
        return $this->loggingContextBusinessKey;
    }

    public function setLoggingContextBusinessKey(?string $loggingContextBusinessKey): ProcessEngineConfigurationImpl
    {
        $this->loggingContextBusinessKey = $loggingContextBusinessKey;
        return $this;
    }

    public function getLoggingContextProcessDefinitionId(): ?string
    {
        return $this->loggingContextProcessDefinitionId;
    }

    public function setLoggingContextProcessDefinitionId(?string $loggingContextProcessDefinitionId): ProcessEngineConfigurationImpl
    {
        $this->loggingContextProcessDefinitionId = $loggingContextProcessDefinitionId;
        return $this;
    }

    public function getLoggingContextProcessDefinitionKey(): ?string
    {
        return $this->loggingContextProcessDefinitionKey;
    }

    public function setLoggingContextProcessDefinitionKey(?string $loggingContextProcessDefinitionKey): ProcessEngineConfigurationImpl
    {
        $this->loggingContextProcessDefinitionKey = $loggingContextProcessDefinitionKey;
        return $this;
    }

    public function getLoggingContextProcessInstanceId(): ?string
    {
        return $this->loggingContextProcessInstanceId;
    }

    public function setLoggingContextProcessInstanceId(?string $loggingContextProcessInstanceId): ProcessEngineConfigurationImpl
    {
        $this->loggingContextProcessInstanceId = $loggingContextProcessInstanceId;
        return $this;
    }

    public function getLoggingContextTenantId(): ?string
    {
        return $this->loggingContextTenantId;
    }

    public function setLoggingContextTenantId(?string $loggingContextTenantId): ProcessEngineConfigurationImpl
    {
        $this->loggingContextTenantId = $loggingContextTenantId;
        return $this;
    }

    public function getLoggingContextEngineName(): ?string
    {
        return $this->loggingContextEngineName;
    }

    public function setLoggingContextEngineName(?string $loggingContextEngineName): ProcessEngineConfigurationImpl
    {
        $this->loggingContextEngineName = $loggingContextEngineName;
        return $this;
    }

    public function getLogLevelBpmnStackTrace(): ?string
    {
        return $this->logLevelBpmnStackTrace;
    }

    public function setLogLevelBpmnStackTrace(?string $logLevelBpmnStackTrace): ProcessEngineConfigurationImpl
    {
        $this->logLevelBpmnStackTrace = $logLevelBpmnStackTrace;
        return $this;
    }

    public function isInitializeTelemetry(): bool
    {
        return $this->initializeTelemetry;
    }

    public function setInitializeTelemetry(bool $telemetryInitialized): ProcessEngineConfigurationImpl
    {
        $this->initializeTelemetry = $telemetryInitialized;
        return $this;
    }

    public function getTelemetryEndpoint(): ?string
    {
        return $this->telemetryEndpoint;
    }

    public function setTelemetryEndpoint(?string $telemetryEndpoint): ProcessEngineConfigurationImpl
    {
        $this->telemetryEndpoint = $telemetryEndpoint;
        return $this;
    }

    public function getTelemetryRequestRetries(): int
    {
        return $this->telemetryRequestRetries;
    }

    public function setTelemetryRequestRetries(int $telemetryRequestRetries): ProcessEngineConfigurationImpl
    {
        $this->telemetryRequestRetries = $telemetryRequestRetries;
        return $this;
    }

    public function getTelemetryReportingPeriod(): int
    {
        return $this->telemetryReportingPeriod;
    }

    public function setTelemetryReportingPeriod(int $telemetryReportingPeriod): ProcessEngineConfigurationImpl
    {
        $this->telemetryReportingPeriod = $telemetryReportingPeriod;
        return $this;
    }

    public function getTelemetryReporter(): TelemetryReporter
    {
        return $this->telemetryReporter;
    }

    public function setTelemetryReporter(TelemetryReporter $telemetryReporter): ProcessEngineConfigurationImpl
    {
        $this->telemetryReporter = $telemetryReporter;
        return $this;
    }

    public function isTelemetryReporterActivate(): bool
    {
        return $this->isTelemetryReporterActivate;
    }

    public function setTelemetryReporterActivate(bool $isTelemetryReporterActivate): ProcessEngineConfigurationImpl
    {
        $this->isTelemetryReporterActivate = $isTelemetryReporterActivate;
        return $this;
    }

    public function getTelemetryHttpConnector()
    {
        return $this->telemetryHttpConnector;
    }

    public function setTelemetryHttpConnector($telemetryHttp): ProcessEngineConfigurationImpl
    {
        $this->telemetryHttpConnector = $telemetryHttp;
        return $this;
    }

    public function getTelemetryData(): TelemetryDataImpl
    {
        return $this->telemetryData;
    }

    public function setTelemetryData(TelemetryDataImpl $telemetryData): ProcessEngineConfigurationImpl
    {
        $this->telemetryData = $telemetryData;
        return $this;
    }

    public function getTelemetryRequestTimeout(): int
    {
        return $this->telemetryRequestTimeout;
    }

    public function setTelemetryRequestTimeout(int $telemetryRequestTimeout): ProcessEngineConfigurationImpl
    {
        $this->telemetryRequestTimeout = $telemetryRequestTimeout;
        return $this;
    }

    public function setCommandRetries(int $commandRetries): ProcessEngineConfigurationImpl
    {
        $this->commandRetries = $commandRetries;
        return $this;
    }

    public function getCommandRetries(): int
    {
        return $this->commandRetries;
    }

    /**
     * @return a exception code interceptor. The interceptor is not registered in case
     * {@code disableExceptionCode} is configured to {@code true}.
     */
    protected function getExceptionCodeInterceptor(): ExceptionCodeInterceptor
    {
        return new ExceptionCodeInterceptor($this->builtinExceptionCodeProvider, $this->customExceptionCodeProvider);
    }
}
