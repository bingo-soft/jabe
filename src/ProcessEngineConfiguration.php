<?php

namespace Jabe;

use Jabe\Impl\{
    BootstrapEngineCommand,
    HistoryLevelSetupCommand,
    SchemaOperationsProcessEngineBuild
};
use Jabe\Impl\Persistence\Entity\JobEntity;
use Jabe\Impl\Cfg\StandaloneProcessEngineConfiguration;
use Jabe\Variable\Type\ValueTypeResolverInterface;
use Jabe\Identity\PasswordPolicyInterface;
use Jabe\Runtime\DeserializationTypeValidatorInterface;
use Jabe\Impl\Telemetry\TelemetryRegistry;
use MyBatis\DataSource\DataSourceInterface;

abstract class ProcessEngineConfiguration
{
    /**
     * Checks the version of the DB schema against the library when
     * the process engine is being created and throws an exception
     * if the versions don't match.
     */
    public const DB_SCHEMA_UPDATE_FALSE = "false";

    /**
     * Creates the schema when the process engine is being created and
     * drops the schema when the process engine is being closed.
     */
    public const DB_SCHEMA_UPDATE_CREATE_DROP = "create-drop";

    /**
     * Upon building of the process engine, a check is performed and
     * an update of the schema is performed if it is necessary.
     */
    public const DB_SCHEMA_UPDATE_TRUE = "true";

    /**
     * Value for {@link #setHistory(String)} to ensure that no history is being recorded.
     */
    public const HISTORY_NONE = "none";
    /**
     * Value for {@link #setHistory(String)} to ensure that only historic process instances and
     * historic activity instances are being recorded.
     * This means no details for those entities.
     */
    public const HISTORY_ACTIVITY = "activity";

    /**
     * Value for {@link #setHistory(String)} to ensure that only historic process instances,
     * historic activity instances and submitted form property values are being recorded.
     */
    public const HISTORY_AUDIT = "audit";
    /**
     * Value for {@link #setHistory(String)} to ensure that all historic information is
     * being recorded, including the variable updates.
     */
    public const HISTORY_FULL = "full";

    /**
     * Value for {@link #setHistory(String)}. Choosing auto causes the configuration to choose the level
     * already present on the database. If none can be found, "audit" is taken.
     */
    public const HISTORY_AUTO = "auto";

    /**
     * The default history level that is used when no history level is configured
     */
    public const HISTORY_DEFAULT = self::HISTORY_AUDIT;

    /**
     * History cleanup is performed based on end time.
     */
    public const HISTORY_CLEANUP_STRATEGY_END_TIME_BASED = "endTimeBased";

    /**
     * History cleanup is performed based on removal time.
     */
    public const HISTORY_CLEANUP_STRATEGY_REMOVAL_TIME_BASED = "removalTimeBased";

    /**
     * Removal time for historic entities is set on execution start.
     */
    public const HISTORY_REMOVAL_TIME_STRATEGY_START = "start";

    /**
     * Removal time for historic entities is set if execution has been ended.
     */
    public const HISTORY_REMOVAL_TIME_STRATEGY_END = "end";

    /**
     * Removal time for historic entities is not set.
     */
    public const HISTORY_REMOVAL_TIME_STRATEGY_NONE = "none";

    /**
     * Always enables check for {@link Authorization#AUTH_TYPE_REVOKE revoke} authorizations.
     * This mode is equal to the < 7.5 behavior.
     *<p />
    * *NOTE:* Checking revoke authorizations is very expensive for resources with a high potential
    * cardinality like tasks or process instances and can render authorized access to the process engine
    * effectively unusable on most databases. You are therefore strongly discouraged from using this mode.
    *
    */
    public const AUTHORIZATION_CHECK_REVOKE_ALWAYS = "always";

    /**
     * Never checks for {@link Authorization#AUTH_TYPE_REVOKE revoke} authorizations. This mode
     * has best performance effectively disables the use of Authorization#AUTH_TYPE_REVOKE.
     * *Note*: It is strongly recommended to use this mode.
     */
    public const AUTHORIZATION_CHECK_REVOKE_NEVER = "never";

    /**
     * This mode only checks for {@link Authorization#AUTH_TYPE_REVOKE revoke} authorizations if at least
     * one revoke authorization currently exits for the current user or one of the groups the user is a member
     * of. To achieve this it is checked once per command whether potentially applicable revoke authorizations
     * exist. Based on the outcome, the authorization check then uses revoke or not.
     *<p />
    * *NOTE:* Checking revoke authorizations is very expensive for resources with a high potential
    * cardinality like tasks or process instances and can render authorized access to the process engine
    * effectively unusable on most databases.
    */
    public const AUTHORIZATION_CHECK_REVOKE_AUTO = "auto";

    protected $processEngineName = ProcessEngines::NAME_DEFAULT;
    protected $idBlockSize = 100;
    protected $history = self::HISTORY_DEFAULT;
    protected $jobExecutorActivate;
    protected $jobExecutorDeploymentAware = false;
    protected $jobExecutorPreferTimerJobs = false;
    protected $jobExecutorAcquireByDueDate = false;
    protected $jobExecutorAcquireByPriority = false;

    protected $ensureJobDueDateNotNull = false;
    protected $producePrioritizedJobs = true;
    protected $producePrioritizedExternalTasks = true;

    /**
     * The flag will be used inside the method "JobManager#send()". It will be used to decide whether to notify the
     * job executor that a new job has been created. It will be used for performance improvement, so that the new job could
     * be executed in some situations immediately.
     */
    protected $hintJobExecutor = true;

    protected $mailServerHost;
    protected $mailServerUsername; // by default no name and password are provided, which
    protected $mailServerPassword; // means no authentication for mail server
    protected $mailServerPort;
    protected $useTLS;
    protected $mailServerDefaultFrom;

    protected $databaseType;
    protected $databaseVendor;
    protected $databaseVersion;
    protected $databaseSchemaUpdate = self::DB_SCHEMA_UPDATE_FALSE;
    //default Postgresql connection run inside the container
    protected $dbDriver = 'pdo_pgsql';
    protected $dbUrl = 'pgsql:host=localhost;port=5432;dbname=engine;';
    protected $dbUsername = 'postgres';
    protected $dbPassword = 'postgres';
    protected $dbMaxActiveConnections;
    protected $dbMaxIdleConnections;
    protected $dbMaxCheckoutTime;
    protected $dbMaxWaitTime;
    protected $dbPingEnabled = false;
    protected $dbPingQuery = null;
    protected $dbPingConnectionNotUsedFor;
    protected $dataSource;

    protected $schemaOperationsCommand;
    protected $bootstrapCommand;
    protected $historyLevelCommand;
    protected $transactionsExternallyManaged = false;
    /** the number of seconds the db driver will wait for a response from the database */
    protected $dbStatementTimeout;
    protected $dbBatchProcessing = true;

    protected $persistenceUnitName;
    //protected $entityManagerFactory;
    protected $handleTransaction;
    //protected $closeEntityManager;
    protected $defaultNumberOfRetries = JobEntity::DEFAULT_RETRIES;

    protected $createIncidentOnFailedJobEnabled = true;

    /**
     * configuration of password policy
     */
    protected $enablePasswordPolicy;
    protected $passwordPolicy;

    /**
     * switch for controlling whether the process engine performs authorization checks.
     * The default value is false.
     */
    protected $authorizationEnabled = false;

    /**
     * Provides the default task permission for the user related to a task
     * User can be related to a task in the following ways
     * - Candidate user
     * - Part of candidate group
     * - Assignee
     * - Owner
     * The default value is UPDATE.
     */
    protected $defaultUserPermissionNameForTask = "UPDATE";

    /**
     * <p>The following flag <code>authorizationEnabledForCustomCode</code> will
     * only be taken into account iff <code>authorizationEnabled</code> is set
     * <code>true</code>.</p>
     *
     * <p>If the value of the flag <code>authorizationEnabledForCustomCode</code>
     * is set <code>true</code> then an authorization check will be performed by
     * executing commands inside custom code (e.g. inside JavaDelegate).</p>
     *
     * <p>The default value is <code>false</code>.</p>
     *
     */
    protected $authorizationEnabledForCustomCode = false;

    /**
     * If the value of this flag is set <code>true</code> then the process engine
     * performs tenant checks to ensure that an authenticated user can only access
     * data that belongs to one of his tenants.
     */
    protected $tenantCheckEnabled = true;

    protected $valueTypeResolver;

    protected $authorizationCheckRevokes = self::AUTHORIZATION_CHECK_REVOKE_AUTO;

    /**
     * A parameter used for defining acceptable values for the User, Group
     * and Tenant IDs. The pattern can be defined by using the standard
     * Java Regular Expression syntax should be used.
     *
     * <p>By default only alphanumeric values (or 'admin') will be accepted.</p>
     */
    protected $generalResourceWhitelistPattern =  "[a-zA-Z0-9]+|admin";

    /**
     * A parameter used for defining acceptable values for the User IDs.
     * The pattern can be defined by using the standard Java Regular
     * Expression syntax should be used.
     *
     * <p>If not defined, the general pattern is used. Only alphanumeric
     * values (or 'camunda-admin') will be accepted.</p>
     */
    protected $userResourceWhitelistPattern;

    /**
     * A parameter used for defining acceptable values for the Group IDs.
     * The pattern can be defined by using the standard Java Regular
     * Expression syntax should be used.
     *
     * <p>If not defined, the general pattern is used. Only alphanumeric
     * values (or 'camunda-admin') will be accepted.</p>
     */
    protected $groupResourceWhitelistPattern;

    /**
     * A parameter used for defining acceptable values for the Tenant IDs.
     * The pattern can be defined by using the standard Java Regular
     * Expression syntax should be used.
     *
     * <p>If not defined, the general pattern is used. Only alphanumeric
     * values (or 'camunda-admin') will be accepted.</p>
     */
    protected $tenantResourceWhitelistPattern;

    /**
     * If the value of this flag is set <code>true</code> then the process engine
     * throws ProcessEngineException when no catching boundary event was
     * defined for an error event.
     *
     * <p>The default value is <code>false</code>.</p>
     */
    protected $enableExceptionsAfterUnhandledBpmnError = false;

    /**
     * If the value of this flag is set to <code>false</code>, OptimisticLockingExceptions
     * are not skipped for UPDATE or DELETE operations applied to historic entities.
     *
     * <p>The default value is <code>true</code>.</p>
     */
    protected $skipHistoryOptimisticLockingExceptions = true;

    /**
     * If the value of this flag is set to <code>true</code>,
     * READ_INSTANCE_VARIABLE,
     * READ_HISTORY_VARIABLE, or
     * READ_TASK_VARIABLE on Process Definition resource, and
     * READ_VARIABLE on Task resource
     * READ_VARIABLE on Historic Task Instance resource
     * will be required to fetch variables when the authorizations are enabled.
     */
    protected $enforceSpecificVariablePermission = false;

    /**
     * Specifies which permissions will not be taken into account in the
     * authorizations checks if authorization is enabled.
     */
    protected $disabledPermissions = [];

    /**
     * If the value of this flag is set to <code>false</code> exceptions that occur
     * during command execution will not be logged before re-thrown. This can prevent
     * multiple logs of the same exception (e.g. exceptions that occur during job execution)
     * but can also hide valuable debugging/rootcausing information.
     */
    protected $enableCmdExceptionLogging = true;

    /**
     * If the value of this flag is set to <code>true</code> exceptions that occur
     * during the execution of a job that still has retries left will not be logged.
     * If the job does not have any retries left, the exception will still be logged
     * on logging level WARN.
     */
    protected $enableReducedJobExceptionLogging = false;

    /** Specifies which classes are allowed for deserialization */
    protected $deserializationAllowedClasses;

    /** Specifies which packages are allowed for deserialization */
    protected $deserializationAllowedPackages;

    /** Validates types before deserialization */
    protected $deserializationTypeValidator;

    /** Indicates whether type validation should be done before deserialization */
    protected $deserializationTypeValidationEnabled = false;

    /** An unique installation identifier */
    protected $installationId;

    protected $telemetryRegistry;

    /**
     * On failing activities we can skip output mapping. This might be helpful if output mapping uses variables that might not
     * be available on failure (e.g. with external tasks or RPA tasks).
     */
    protected $skipOutputMappingOnCanceledActivities = false;

    protected function __construct()
    {
        $this->mailServerHost = getenv('MAIL_HOST', true);
        $this->mailServerUsername = getenv('MAIL_USER', true); // by default no name and password are provided, which
        $this->mailServerPassword = getenv('MAIL_PASSWORD', true); // means no authentication for mail server
        $this->mailServerPort = getenv('MAIL_PORT', true);
        $this->useTLS = json_decode(getenv('MAIL_USE_TLS', true));
        $this->mailServerDefaultFrom = getenv('MAIL_USE_FROM', true);

        /*$this->dbDriver = getenv('DB_DRIVER', true);
        $this->dbHost = getenv('DB_HOST', true);
        $this->dbUsername = getenv('DB_USER', true);
        $this->dbPassword = getenv('DB_PASSWORD', true);
        $this->dbName = getenv('DB_NAME', true);
        $this->dbPort = getenv('DB_PORT', true);*/

        $this->schemaOperationsCommand = new SchemaOperationsProcessEngineBuild();
        $this->bootstrapCommand = new BootstrapEngineCommand();
        $this->historyLevelCommand = new HistoryLevelSetupCommand();
    }

    abstract public function buildProcessEngine();

    public static function createStandaloneProcessEngineConfiguration(): ProcessEngineConfiguration
    {
        return new StandaloneProcessEngineConfiguration();
    }

    // getters and setters //////////////////////////////////////////////////////

    public function getProcessEngineName(): string
    {
        return $this->processEngineName;
    }

    public function setProcessEngineName(string $processEngineName): ProcessEngineConfiguration
    {
        $this->processEngineName = $processEngineName;
        return $this;
    }

    public function getIdBlockSize(): int
    {
        return $this->idBlockSize;
    }

    public function setIdBlockSize(int $idBlockSize): ProcessEngineConfiguration
    {
        $this->idBlockSize = $idBlockSize;
        return $this;
    }

    public function getHistory(): string
    {
        return $this->history;
    }

    public function setHistory(string $history): ProcessEngineConfiguration
    {
        $this->history = $history;
        return $this;
    }

    public function getMailServerHost(): string
    {
        return $this->mailServerHost;
    }

    public function setMailServerHost(string $mailServerHost): ProcessEngineConfiguration
    {
        $this->mailServerHost = $mailServerHost;
        return $this;
    }

    public function getMailServerUsername(): string
    {
        return $this->mailServerUsername;
    }

    public function setMailServerUsername(string $mailServerUsername): ProcessEngineConfiguration
    {
        $this->mailServerUsername = $mailServerUsername;
        return $this;
    }

    public function getMailServerPassword(): string
    {
        return $this->mailServerPassword;
    }

    public function setMailServerPassword(string $mailServerPassword): ProcessEngineConfiguration
    {
        $this->mailServerPassword = $mailServerPassword;
        return $this;
    }

    public function getMailServerPort(): int
    {
        return $this->mailServerPort;
    }

    public function setMailServerPort(int $mailServerPort): ProcessEngineConfiguration
    {
        $this->mailServerPort = $mailServerPort;
        return $this;
    }

    public function getMailServerUseTLS(): bool
    {
        return $this->useTLS;
    }

    public function setMailServerUseTLS(bool $useTLS): ProcessEngineConfiguration
    {
        $this->useTLS = $useTLS;
        return $this;
    }

    public function getMailServerDefaultFrom(): string
    {
        return $this->mailServerDefaultFrom;
    }

    public function setMailServerDefaultFrom(string $mailServerDefaultFrom): ProcessEngineConfiguration
    {
        $this->mailServerDefaultFrom = $mailServerDefaultFrom;
        return $this;
    }

    public function getDatabaseType(): string
    {
        return $this->databaseType;
    }

    public function setDatabaseType(string $databaseType): ProcessEngineConfiguration
    {
        $this->databaseType = $databaseType;
        return $this;
    }

    public function getDatabaseVendor(): string
    {
        return $this->databaseVendor;
    }

    public function setDatabaseVendor(string $databaseVendor): ProcessEngineConfiguration
    {
        $this->databaseVendor = $databaseVendor;
        return $this;
    }

    public function getDatabaseVersion(): string
    {
        return $this->databaseVersion;
    }

    public function setDatabaseVersion(string $databaseVersion): ProcessEngineConfiguration
    {
        $this->databaseVersion = $databaseVersion;
        return $this;
    }

    public function getDatabaseSchemaUpdate(): string
    {
        return $this->databaseSchemaUpdate;
    }

    public function setDatabaseSchemaUpdate(string $databaseSchemaUpdate): ProcessEngineConfiguration
    {
        $this->databaseSchemaUpdate = $databaseSchemaUpdate;
        return $this;
    }

    public function getDataSource(): DataSourceInterface
    {
        return $this->dataSource;
    }

    public function setDataSource(DataSourceInterface $dataSource): ProcessEngineConfiguration
    {
        $this->dataSource = $dataSource;
        return $this;
    }

    public function getSchemaOperationsCommand(): SchemaOperationsCommand
    {
        return $this->schemaOperationsCommand;
    }

    public function setSchemaOperationsCommand(SchemaOperationsCommand $schemaOperationsCommand): void
    {
        $this->schemaOperationsCommand = $schemaOperationsCommand;
    }

    public function getProcessEngineBootstrapCommand(): ProcessEngineBootstrapCommand
    {
        return $this->bootstrapCommand;
    }

    public function setProcessEngineBootstrapCommand(ProcessEngineBootstrapCommand $bootstrapCommand): void
    {
        $this->bootstrapCommand = $bootstrapCommand;
    }

    public function getHistoryLevelCommand(): HistoryLevelSetupCommand
    {
        return $this->historyLevelCommand;
    }

    public function setHistoryLevelCommand(HistoryLevelSetupCommand $historyLevelCommand): void
    {
        $this->historyLevelCommand = $historyLevelCommand;
    }

    public function getDbDriver(): string
    {
        return $this->dbDriver;
    }

    public function setDbDriver(string $dbDriver): ProcessEngineConfiguration
    {
        $this->dbDriver = $dbDriver;
        return $this;
    }

    public function getDbHost(): string
    {
        return $this->dbHost;
    }

    public function setDbHost(string $dbHost): ProcessEngineConfiguration
    {
        $this->dbHost = $dbHost;
        return $this;
    }

    public function getDbUsername(): string
    {
        return $this->dbUsername;
    }

    public function setDbUsername(string $dbUsername): ProcessEngineConfiguration
    {
        $this->dbUsername = $dbUsername;
        return $this;
    }

    public function getDbPassword(): string
    {
        return $this->dbPassword;
    }

    public function setDbPassword(string $dbPassword): ProcessEngineConfiguration
    {
        $this->dbPassword = $dbPassword;
        return $this;
    }

    public function isTransactionsExternallyManaged(): bool
    {
        return $this->transactionsExternallyManaged;
    }

    public function setTransactionsExternallyManaged(bool $transactionsExternallyManaged): ProcessEngineConfiguration
    {
        $this->transactionsExternallyManaged = $transactionsExternallyManaged;
        return $this;
    }

    public function getDbMaxActiveConnections(): int
    {
        return $this->dbMaxActiveConnections;
    }

    public function setDbMaxActiveConnections(int $dbMaxActiveConnections): ProcessEngineConfiguration
    {
        $this->dbMaxActiveConnections = $dbMaxActiveConnections;
        return $this;
    }

    public function getDbMaxIdleConnections(): int
    {
        return $this->dbMaxIdleConnections;
    }

    public function setDbMaxIdleConnections(int $dbMaxIdleConnections): int
    {
        $this->dbMaxIdleConnections = $dbMaxIdleConnections;
        return $this;
    }

    public function getDbMaxCheckoutTime(): int
    {
        return $this->dbMaxCheckoutTime;
    }

    public function setDbMaxCheckoutTime(int $dbMaxCheckoutTime): ProcessEngineConfiguration
    {
        $this->dbMaxCheckoutTime = $dbMaxCheckoutTime;
        return $this;
    }

    public function getDbMaxWaitTime(): int
    {
        return $this->dbMaxWaitTime;
    }

    public function setDbMaxWaitTime(int $dbMaxWaitTime): ProcessEngineConfiguration
    {
        $this->dbMaxWaitTime = $dbMaxWaitTime;
        return $this;
    }

    public function isDbPingEnabled(): bool
    {
        return $this->dbPingEnabled;
    }

    public function setDbPingEnabled(bool $dbPingEnabled): ProcessEngineConfiguration
    {
        $this->dbPingEnabled = $dbPingEnabled;
        return $this;
    }

    public function getDbPingQuery(): string
    {
        return $this->dbPingQuery;
    }

    public function setDbPingQuery(string $dbPingQuery): ProcessEngineConfiguration
    {
        $this->dbPingQuery = $dbPingQuery;
        return $this;
    }

    public function getDbPingConnectionNotUsedFor(): int
    {
        return $this->dbPingConnectionNotUsedFor;
    }

    public function setDbPingConnectionNotUsedFor(int $dbPingNotUsedFor): ProcessEngineConfiguration
    {
        $this->dbPingConnectionNotUsedFor = $dbPingNotUsedFor;
        return $this;
    }

    /** Gets the number of seconds the db driver will wait for a response from the database. */
    public function getDbStatementTimeout(): int
    {
        return $this->dbStatementTimeout;
    }

    /** Sets the number of seconds the db driver will wait for a response from the database. */
    public function setDbStatementTimeout(int $dbStatementTimeout): ProcessEngineConfiguration
    {
        $this->dbStatementTimeout = $dbStatementTimeout;
        return $this;
    }

    public function isDbBatchProcessing(): bool
    {
        return $this->dbBatchProcessing;
    }

    public function setDbBatchProcessing(bool $dbBatchProcessing): ProcessEngineConfiguration
    {
        $this->dbBatchProcessing = $dbBatchProcessing;
        return $this;
    }

    public function isJobExecutorActivate(): bool
    {
        return $this->jobExecutorActivate;
    }

    public function setJobExecutorActivate(bool $jobExecutorActivate): ProcessEngineConfiguration
    {
        $this->jobExecutorActivate = $jobExecutorActivate;
        return $this;
    }

    public function isJobExecutorDeploymentAware(): bool
    {
        return $this->jobExecutorDeploymentAware;
    }

    public function setJobExecutorDeploymentAware(bool $jobExecutorDeploymentAware): ProcessEngineConfiguration
    {
        $this->jobExecutorDeploymentAware = $jobExecutorDeploymentAware;
        return $this;
    }

    public function isJobExecutorAcquireByDueDate(): bool
    {
        return $this->jobExecutorAcquireByDueDate;
    }

    public function setJobExecutorAcquireByDueDate(bool $jobExecutorAcquireByDueDate): ProcessEngineConfiguration
    {
        $this->jobExecutorAcquireByDueDate = $jobExecutorAcquireByDueDate;
        return $this;
    }

    public function isJobExecutorPreferTimerJobs(): bool
    {
        return $this->jobExecutorPreferTimerJobs;
    }

    public function setJobExecutorPreferTimerJobs(bool $jobExecutorPreferTimerJobs): ProcessEngineConfiguration
    {
        $this->jobExecutorPreferTimerJobs = $jobExecutorPreferTimerJobs;
        return $this;
    }

    public function isHintJobExecutor(): bool
    {
        return $this->hintJobExecutor;
    }

    public function setHintJobExecutor(bool $hintJobExecutor): ProcessEngineConfiguration
    {
        $this->hintJobExecutor = $hintJobExecutor;
        return $this;
    }

    public function isHandleTransaction(): bool
    {
        return $this->handleTransaction;
    }

    public function setHandleTransaction(bool $handleTransaction): ProcessEngineConfiguration
    {
        $this->handleTransaction = $handleTransaction;
        return $this;
    }

    /*public function isCloseEntityManager(): bool
    {
        return $this->closeEntityManager;
    }

    public function setCloseEntityManager(bool $closeEntityManager): ProcessEngineConfiguration
    {
        $this->closeEntityManager = $closeEntityManager;
        return $this;
    }*/

    public function getPersistenceUnitName(): string
    {
        return $this->persistenceUnitName;
    }

    public function setPersistenceUnitName(string $persistenceUnitName): void
    {
        $this->persistenceUnitName = $persistenceUnitName;
    }

    public function isCreateIncidentOnFailedJobEnabled(): bool
    {
        return $this->createIncidentOnFailedJobEnabled;
    }

    public function setCreateIncidentOnFailedJobEnabled(bool $createIncidentOnFailedJobEnabled): ProcessEngineConfiguration
    {
        $this->createIncidentOnFailedJobEnabled = $createIncidentOnFailedJobEnabled;
        return $this;
    }

    public function isAuthorizationEnabled(): bool
    {
        return $this->authorizationEnabled;
    }

    public function setAuthorizationEnabled(bool $isAuthorizationChecksEnabled): ProcessEngineConfiguration
    {
        $this->authorizationEnabled = $isAuthorizationChecksEnabled;
        return $this;
    }

    public function getDefaultUserPermissionNameForTask(): string
    {
        return $this->defaultUserPermissionNameForTask;
    }

    public function setDefaultUserPermissionNameForTask(string $defaultUserPermissionNameForTask): ProcessEngineConfiguration
    {
        $this->defaultUserPermissionNameForTask = $defaultUserPermissionNameForTask;
        return $this;
    }

    public function isAuthorizationEnabledForCustomCode(): bool
    {
        return $this->authorizationEnabledForCustomCode;
    }

    public function setAuthorizationEnabledForCustomCode(bool $authorizationEnabledForCustomCode): ProcessEngineConfiguration
    {
        $this->authorizationEnabledForCustomCode = $authorizationEnabledForCustomCode;
        return $this;
    }

    public function isTenantCheckEnabled(): bool
    {
        return $this->tenantCheckEnabled;
    }

    public function setTenantCheckEnabled(bool $isTenantCheckEnabled): ProcessEngineConfiguration
    {
        $this->tenantCheckEnabled = $isTenantCheckEnabled;
        return $this;
    }

    public function getGeneralResourceWhitelistPattern(): string
    {
        return $this->generalResourceWhitelistPattern;
    }

    public function setGeneralResourceWhitelistPattern(string $generalResourceWhitelistPattern): void
    {
        $this->generalResourceWhitelistPattern = $generalResourceWhitelistPattern;
    }

    public function getUserResourceWhitelistPattern(): string
    {
        return $this->userResourceWhitelistPattern;
    }

    public function setUserResourceWhitelistPattern(string $userResourceWhitelistPattern): void
    {
        $this->userResourceWhitelistPattern = $userResourceWhitelistPattern;
    }

    public function getGroupResourceWhitelistPattern(): string
    {
        return $this->groupResourceWhitelistPattern;
    }

    public function setGroupResourceWhitelistPattern(string $groupResourceWhitelistPattern): void
    {
        $this->groupResourceWhitelistPattern = $groupResourceWhitelistPattern;
    }

    public function getTenantResourceWhitelistPattern(): string
    {
        return $this->tenantResourceWhitelistPattern;
    }

    public function setTenantResourceWhitelistPattern(string $tenantResourceWhitelistPattern): void
    {
        $this->tenantResourceWhitelistPattern = $tenantResourceWhitelistPattern;
    }

    public function getDefaultNumberOfRetries(): int
    {
        return $this->defaultNumberOfRetries;
    }

    public function setDefaultNumberOfRetries(int $defaultNumberOfRetries): void
    {
        $this->defaultNumberOfRetries = $defaultNumberOfRetries;
    }

    public function getValueTypeResolver(): ValueTypeResolverInterface
    {
        return $this->valueTypeResolver;
    }

    public function setValueTypeResolver(ValueTypeResolverInterface $valueTypeResolver): ProcessEngineConfiguration
    {
        $this->valueTypeResolver = $valueTypeResolver;
        return $this;
    }

    public function isEnsureJobDueDateNotNull(): bool
    {
        return $this->ensureJobDueDateNotNull;
    }

    public function setEnsureJobDueDateNotNull(bool $ensureJobDueDateNotNull): void
    {
        $this->ensureJobDueDateNotNull = $ensureJobDueDateNotNull;
    }

    public function isProducePrioritizedJobs(): bool
    {
        return $this->producePrioritizedJobs;
    }

    public function setProducePrioritizedJobs(bool $producePrioritizedJobs): void
    {
        $this->producePrioritizedJobs = $producePrioritizedJobs;
    }

    public function isJobExecutorAcquireByPriority(): bool
    {
        return $this->jobExecutorAcquireByPriority;
    }

    public function setJobExecutorAcquireByPriority(bool $jobExecutorAcquireByPriority): void
    {
        $this->jobExecutorAcquireByPriority = $jobExecutorAcquireByPriority;
    }

    public function isProducePrioritizedExternalTasks(): bool
    {
        return $this->producePrioritizedExternalTasks;
    }

    public function setProducePrioritizedExternalTasks(bool $producePrioritizedExternalTasks): void
    {
        $this->producePrioritizedExternalTasks = $producePrioritizedExternalTasks;
    }

    public function setAuthorizationCheckRevokes(string $authorizationCheckRevokes): bool
    {
        $this->authorizationCheckRevokes = $authorizationCheckRevokes;
    }

    public function getAuthorizationCheckRevokes(): string
    {
        return $this->authorizationCheckRevokes;
    }

    public function isEnableExceptionsAfterUnhandledBpmnError(): bool
    {
        return $this->enableExceptionsAfterUnhandledBpmnError;
    }

    public function setEnableExceptionsAfterUnhandledBpmnError(bool $enableExceptionsAfterUnhandledBpmnError): void
    {
        $this->enableExceptionsAfterUnhandledBpmnError = $enableExceptionsAfterUnhandledBpmnError;
    }

    public function isSkipHistoryOptimisticLockingExceptions(): bool
    {
        return $this->skipHistoryOptimisticLockingExceptions;
    }

    public function setSkipHistoryOptimisticLockingExceptions(bool $skipHistoryOptimisticLockingExceptions): ProcessEngineConfiguration
    {
        $this->skipHistoryOptimisticLockingExceptions = $skipHistoryOptimisticLockingExceptions;
        return $this;
    }

    public function isEnforceSpecificVariablePermission(): bool
    {
        return $this->enforceSpecificVariablePermission;
    }

    public function setEnforceSpecificVariablePermission(bool $ensureSpecificVariablePermission): void
    {
        $this->enforceSpecificVariablePermission = $ensureSpecificVariablePermission;
    }

    public function getDisabledPermissions(): array
    {
        return $this->disabledPermissions;
    }

    public function setDisabledPermissions(array $disabledPermissions): void
    {
        $this->disabledPermissions = $disabledPermissions;
    }

    public function isEnablePasswordPolicy(): bool
    {
        return $this->enablePasswordPolicy;
    }

    public function setEnablePasswordPolicy(bool $enablePasswordPolicy): ProcessEngineConfiguration
    {
        $this->enablePasswordPolicy = $enablePasswordPolicy;
        return $this;
    }

    public function getPasswordPolicy(): PasswordPolicyInterface
    {
        return $this->passwordPolicy;
    }

    public function setPasswordPolicy(PasswordPolicyInterface $passwordPolicy): ProcessEngineConfiguration
    {
        $this->passwordPolicy = $passwordPolicy;
        return $this;
    }

    public function isEnableCmdExceptionLogging(): bool
    {
        return $this->enableCmdExceptionLogging;
    }

    public function setEnableCmdExceptionLogging(bool $enableCmdExceptionLogging): ProcessEngineConfiguration
    {
        $this->enableCmdExceptionLogging = $enableCmdExceptionLogging;
        return $this;
    }

    public function isEnableReducedJobExceptionLogging(): bool
    {
        return $this->enableReducedJobExceptionLogging;
    }

    public function setEnableReducedJobExceptionLogging(bool $enableReducedJobExceptionLogging): ProcessEngineConfiguration
    {
        $this->enableReducedJobExceptionLogging = $enableReducedJobExceptionLogging;
        return $this;
    }

    public function getDeserializationAllowedClasses(): string
    {
        return $this->deserializationAllowedClasses;
    }

    public function setDeserializationAllowedClasses(string $deserializationAllowedClasses): ProcessEngineConfiguration
    {
        $this->deserializationAllowedClasses = $deserializationAllowedClasses;
        return $this;
    }

    public function getDeserializationAllowedPackages(): string
    {
        return $this->deserializationAllowedPackages;
    }

    public function setDeserializationAllowedPackages(string $deserializationAllowedPackages): ProcessEngineConfiguration
    {
        $this->deserializationAllowedPackages = $deserializationAllowedPackages;
        return $this;
    }

    public function getDeserializationTypeValidator(): DeserializationTypeValidatorInterface
    {
        return $this->deserializationTypeValidator;
    }

    public function setDeserializationTypeValidator(DeserializationTypeValidatorInterface $deserializationTypeValidator): ProcessEngineConfiguration
    {
        $this->deserializationTypeValidator = $deserializationTypeValidator;
        return $this;
    }

    public function isDeserializationTypeValidationEnabled(): bool
    {
        return $this->deserializationTypeValidationEnabled;
    }

    public function setDeserializationTypeValidationEnabled(bool $deserializationTypeValidationEnabled): ProcessEngineConfiguration
    {
        $this->deserializationTypeValidationEnabled = $deserializationTypeValidationEnabled;
        return $this;
    }

    public function getInstallationId(): string
    {
        return $this->installationId;
    }

    public function setInstallationId(string $installationId): ProcessEngineConfiguration
    {
        $this->installationId = $installationId;
        return $this;
    }

    public function getTelemetryRegistry(): ?TelemetryRegistry
    {
        return $this->telemetryRegistry;
    }

    public function setTelemetryRegistry(TelemetryRegistry $telemetryRegistry): ProcessEngineConfiguration
    {
        $this->telemetryRegistry = $telemetryRegistry;
        return $this;
    }

    public function isSkipOutputMappingOnCanceledActivities(): bool
    {
        return $this->skipOutputMappingOnCanceledActivities;
    }

    public function setSkipOutputMappingOnCanceledActivities(bool $skipOutputMappingOnCanceledActivities): void
    {
        $this->skipOutputMappingOnCanceledActivities = $skipOutputMappingOnCanceledActivities;
    }
}
