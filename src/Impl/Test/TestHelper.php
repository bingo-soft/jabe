<?php

namespace Jabe\Impl\Test;

use Jabe\{
    HistoryServiceInterface,
    ProcessEngineInterface,
    ProcessEngineConfiguration,
    ProcessEngineException
};
use Jabe\Impl\{
    HistoryLevelSetupCommand,
    ManagementServiceImpl,
    ProcessEngineImpl
    //ProcessEngineLogger
};
//@TODO
//use Jabe\Impl\Application\ProcessApplicationManager;
use Jabe\Impl\Bpmn\Deployer\BpmnDeployer;
use Jabe\Impl\Cfg\{
    IdGeneratorInterface,
    ProcessEngineConfigurationImpl
};
use Jabe\Impl\Db\{
    DbIdGenerator,
    PersistenceSessionInterface
};
use Jabe\Impl\Db\EntityManager\DbEntityManager;
use Jabe\Impl\El\FixedValue;
use Jabe\Impl\History\{
    HistoryLevel,
    HistoryLevelInterface
};
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandInterface
};
use Jabe\Impl\JobExecutor\JobExecutor;
use Jabe\Impl\Management\{
    DatabasePurgeReport,
    PurgeReport
};
use Jabe\Impl\Persistence\Deploy\Cache\CachePurgeReport;
use Jabe\Impl\Persistence\Entity\PropertyEntity;
use Jabe\Impl\Util\{
    ClassNameUtil,
    ReflectUtil
};
use Jabe\Repository\DeploymentBuilderInterface;
use Jabe\Test\{
    Deployment,
    RequiredHistoryLevel
};

abstract class TestHelper
{
    //private static Logger LOG = ProcessEngineLogger.TEST_LOGGER.getLogger();

    public const EMPTY_LINE = "                                                                                           ";

    public const TABLENAMES_EXCLUDED_FROM_DB_CLEAN_CHECK = [
        "ACT_GE_PROPERTY",
        "ACT_GE_SCHEMA_LOG"
    ];

    public static $processEngines = [];

    public const RESOURCE_SUFFIXES = BpmnDeployer::BPMN_RESOURCE_SUFFIXES;

    public static function annotationDeploymentSetUp(ProcessEngineInterface $processEngine, ?string $testClass, ?string $methodName, ?Deployment $deploymentAnnotation = null, bool $onMethod = true, ?array $resources = null): ?string
    {
        if ($resources === null) {
            $method = null;
            $onMethod = true;

            try {
                $method = self::getMethod($testClass, $methodName);
            } catch (\Throwable $e) {
                if ($deploymentAnnotation == null) {
                    // we have neither the annotation, nor can look it up from the method
                    return null;
                }
            }

            if ($deploymentAnnotation == null) {
                $attrs = $method->getAttributes(Deployment::class);
                if (!empty($attrs)) {
                    $deploymentAnnotation = $attrs[0]->newInstance();
                }
            }
            // if not found on method, try on class level
            if ($deploymentAnnotation == null) {
                $onMethod = false;
                $lookForAnnotationClass = new \ReflectionClass($testClass);
                while ($lookForAnnotationClass) {
                    $attrs = $lookForAnnotationClas->getAttributes(Deployment::class);
                    if (!empty($attrs)) {
                        $deploymentAnnotation = $attrs[0]->newInstance();
                    }
                    if ($deploymentAnnotation !== null) {
                        $testClass = $lookForAnnotationClass->name;
                        break;
                    }
                    $lookForAnnotationClass = $lookForAnnotationClass->getParentClass();
                }
            }

            if ($deploymentAnnotation != null) {
                $resources = $deploymentAnnotation->resources();
                //LOG.debug("annotation @Deployment creates deployment for {}.{}", ClassNameUtil.getClassNameWithoutPackage(testClass), methodName);
                return self::annotationDeploymentSetUp($processEngine, $testClass, $methodName, $deploymentAnnotation, $onMethod, $resources);
            } else {
                return null;
            }
        } else {
            if (count($resources) == 0 && $methodName !== null) {
                $name = $onMethod ? $methodName : null;
                $resource = self::getBpmnProcessDefinitionResource($testClass, $name);
                $resources = [ $resource ];
            }

            $deploymentBuilder = $processEngine->getRepositoryService()
                ->createDeployment()
                ->name(ClassNameUtil::getClassNameWithoutPackage($testClass) . "." . $methodName);

            foreach ($resources as $resource) {
                $deploymentBuilder->addClasspathResource($resource);
            }
            return $deploymentBuilder->deploy()->getId();
        }
    }

    public static function annotationDeploymentTearDown(ProcessEngineInterface $processEngine, ?string $deploymentId, ?string $testClass, ?string $methodName): void
    {
        //LOG.debug("annotation @Deployment deletes deployment for {}.{}", ClassNameUtil.getClassNameWithoutPackage(testClass), methodName);
        self::deleteDeployment($processEngine, $deploymentId);
    }

    public static function deleteDeployment(ProcessEngineInterface $processEngine, ?string $deploymentId): void
    {
        if ($deploymentId !== null) {
            $processEngine->getRepositoryService()->deleteDeployment($deploymentId, true, true, true);
        }
    }

    /**
     * get a resource location by convention based on a class (type) and a
     * relative resource name. The return value will be the full classpath
     * location of the type, plus a suffix built from the name parameter:
     * <code>BpmnDeployer.BPMN_RESOURCE_SUFFIXES</code>.
     * The first resource matching a suffix will be returned.
     */
    public static function getBpmnProcessDefinitionResource(?string $type, ?string $name): ?string
    {
        foreach (self::RESOURCE_SUFFIXES as $suffix) {
            $resource = self::createResourceName($type, $name, $suffix);
            $inputStream = ReflectUtil::getResourceAsStream($resource);
            if ($inputStream == null) {
                continue;
            } else {
                return $resource;
            }
        }
        return self::createResourceName($type, $name, BpmnDeployer::BPMN_RESOURCE_SUFFIXES[0]);
    }

    private static function createResourceName(?string $type, ?string $name, ?string $suffix): ?string
    {
        $r = str_replace('.', '/', $type);
        if ($name !== null) {
            $r .= "." . $name;
        }
        return $r . "." . $suffix;
    }

    public static function annotationRequiredHistoryLevelCheck(ProcessEngineInterface $processEngine, ?RequiredHistoryLevel $annotation, ?string $testClass, ?string $methodName): bool
    {
        if ($annotation !== null) {
            return self::historyLevelCheck($processEngine, $annotation);
        } else {
            $annotation = self::getAnnotation($processEngine, $testClass, $methodName, RequiredHistoryLevel::class);
            if ($annotation !== null) {
                return self::historyLevelCheck($processEngine, $annotation);
            } else {
                return true;
            }
        }
    }

    private static function historyLevelCheck(ProcessEngineInterface $processEngine, RequiredHistoryLevel $annotation): bool
    {
        $processEngineConfiguration = $processEngine->getProcessEngineConfiguration();

        $requiredHistoryLevel = self::getHistoryLevelForName($processEngineConfiguration->getHistoryLevels(), $annotation->value());
        $currentHistoryLevel = $processEngineConfiguration->getHistoryLevel();

        return $currentHistoryLevel->getId() >= $requiredHistoryLevel->getId();
    }

    private static function getHistoryLevelForName(array $historyLevels, ?string $name): HistoryLevelInterface
    {
        foreach ($historyLevels as $historyLevel) {
            if (strtolower($historyLevel->getName()) == strtolower($name)) {
                return $historyLevel;
            }
        }
        throw new \Exception("Unknown history level: " . $name);
    }

    public static function annotationRequiredDatabaseCheck(ProcessEngineInterface $processEngine, ?RequiredDatabase $annotation, ?string $testClass, ?string $methodName): bool
    {
        if ($annotation !== null) {
            return self::databaseCheck($processEngine, $annotation);
        } else {
            $annotation = self::getAnnotation($processEngine, $testClass, $methodName, RequiredDatabase::class);
            if ($annotation !== null) {
                return self::databaseCheck($processEngine, $annotation);
            } else {
                return true;
            }
        }
    }

    private static function databaseCheck(ProcessEngineInterface $processEngine, RequiredDatabase $annotation): bool
    {
        $processEngineConfiguration = $processEngine->getProcessEngineConfiguration();
        $actualDbType = $processEngineConfiguration->getDbSqlSessionFactory()->getDatabaseType();

        $excludes = $annotation->excludes();

        if (!empty($excludes)) {
            foreach ($excludes as $exclude) {
                if ($exclude == $actualDbType) {
                    return false;
                }
            }
        }

        $includes = $annotation->includes();

        if (!empty($includes)) {
            foreach ($includes as $include) {
                if ($include == $actualDbType) {
                    return true;
                }
            }
            return false;
        } else {
            return true;
        }
    }

    private static function getAnnotation(ProcessEngineInterface $processEngine, ?string $testClass, ?string $methodName, ?string $annotationClass)
    {
        $method = null;
        $annotation = null;

        try {
            $method = self::getMethod($testClass, $methodName);
            $attrs = $method->getAttributes($annotationClass);
            if (!empty($attrs)) {
                $annotation = $attrs->newInstance();
            }
        } catch (\Throwable $e) {
            // - ignore if we cannot access the method
            // - just try again with the class
            // => can for example be the case for parameterized tests where methodName does not correspond to the actual method name
            //    (note that method-level annotations still work in this
            //     scenario due to Description#getAnnotation in annotationRequiredHistoryLevelCheck)
        }

        // if not found on method, try on class level
        if ($annotation == null) {
            $clazz = new \ReflectionClass($testClass);
            $attrs = $clazz->getAttributes($annotationClass);
            if (!empty($attrs)) {
                $annotation = $attrs->newInstance();
            }
        }
        return $annotation;
    }

    protected static function getMethod(?string $clazz, ?string $methodName): \ReflectionMethod
    {
        try {
            return (new \ReflectionClass($clazz))->getMethod($methodName);
        } catch (\Throwable $e) {
            $parent = (new \ReflectionClass($clazz))->getParentClass();
            if ($parent) {
                return self::getMethod($parent->name, $methodName);
            } else {
                throw $e;
            }
        }
    }

    /**
     * Ensures that the deployment cache and database is clean after a test. If not the cache
     * and database will be cleared.
     *
     * @param processEngine the {@link ProcessEngine} to test
     * @param fail if true the method will throw an {@link AssertionError} if the deployment cache or database is not clean
     * @throws AssertionError if the deployment cache or database was not clean
     */
    public static function assertAndEnsureCleanDbAndCache(ProcessEngineInterface $processEngine, ?bool $fail = true): ?string
    {
        $processEngineConfiguration = $processEngine->getProcessEngineConfiguration();

        // clear user operation log in case some operations are
        // executed with an authenticated user
        self::clearUserOperationLog($processEngineConfiguration);

        //LOG.debug("verifying that db is clean after test");
        $purgeReport = $processEngine->getManagementService()->purge();

        $paRegistrationMessage = self::assertAndEnsureNoProcessApplicationsRegistered($processEngine);

        $message = "";
        $cachePurgeReport = $purgeReport->getCachePurgeReport();
        if (!empty($cachePurgeReport)) {
            $message .= "Deployment cache is not clean:\n"
                        . $cachePurgeReport->getPurgeReportAsString();
        } else {
            //LOG.debug("Deployment cache was clean.");
        }
        $databasePurgeReport = $purgeReport->getDatabasePurgeReport();
        if (!empty($databasePurgeReport)) {
            $message .= "Database is not clean:\n"
                        . $databasePurgeReport->getPurgeReportAsString();
        } else {
            //LOG.debug(
            //    purgeReport.getDatabasePurgeReport().isDbContainsLicenseKey() ? "Database contains license key but is considered clean." : "Database was clean.");
        }
        if ($paRegistrationMessage !== null) {
            $message .= $paRegistrationMessage;
        }

        if ($fail && strlen($message) > 0) {
            throw new \Exception($message);
        }

        return $message;
    }

    /**
     * Ensures that the deployment cache is empty after a test. If not the cache
     * will be cleared.
     *
     * @param processEngine the {@link ProcessEngine} to test
     * @param fail if true the method will throw an {@link AssertionError} if the deployment cache is not clean
     * @return the deployment cache summary if fail is set to false or null if deployment cache was clean
     * @throws AssertionError if the deployment cache was not clean and fail is set to true
     */
    public static function assertAndEnsureCleanDeploymentCache(ProcessEngineInterface $processEngine, bool $fail = true): ?string
    {
        $outputMessage = "";
        $processEngineConfiguration = $processEngine->getProcessEngineConfiguration();
        $cachePurgeReport = $processEngineConfiguration->getDeploymentCache()->purgeCache();

        $outputMessage .= $cachePurgeReport->getPurgeReportAsString();
        if (strlen($outputMessage) > 0) {
            $outputMessage = "Deployment cache not clean:\n" . $outputMessage;
            //LOG.error(outputMessage.toString());

            if ($fail) {
                throw new \Exception($outputMessage);
            }

            return $outputMessage;
        } else {
            //LOG.debug("Deployment cache was clean");
            return null;
        }
    }

    public static function assertAndEnsureNoProcessApplicationsRegistered(ProcessEngineInterface $processEngine): ?string
    {
        $engineConfiguration = $processEngine->getProcessEngineConfiguration();
        $processApplicationManager = $engineConfiguration->getProcessApplicationManager();

        if ($processApplicationManager->hasRegistrations()) {
            $processApplicationManager->clearRegistrations();
            return "There are still process applications registered";
        } else {
            return null;
        }
    }

    public static function waitForJobExecutorToProcessAllJobs(ProcessEngineConfigurationImpl $processEngineConfiguration, int $maxMillisToWait, int $intervalMillis): void
    {
        $jobExecutor = $processEngineConfiguration->getJobExecutor();
        $jobExecutor->start();

        try {
            $areJobsAvailable = true;
            $isTimeLimitExceeded = false;
            try {
                $cur = time();
                while ($areJobsAvailable && !$isTimeLimitExceeded) {
                    usleep($intervalMillis * 1000);
                    $areJobsAvailable = self::areJobsAvailable($processEngineConfiguration);
                    $isTimeLimitExceeded = (time() - $cur) * 1000 >= $maxMillisToWait;
                }
            } catch (\Throwable $e) {
            } finally {
            }
            if ($areJobsAvailable) {
                throw new ProcessEngineException("time limit of " . $maxMillisToWait . " was exceeded");
            }
        } finally {
            $jobExecutor->shutdown();
        }
    }

    public static function areJobsAvailable(ProcessEngineConfigurationImpl $processEngineConfiguration): bool
    {
        return !empty(
            $processEngineConfiguration
            ->getManagementService()
            ->createJobQuery()
            ->executable()
            ->list()
        );
    }

    public static function resetIdGenerator(ProcessEngineConfigurationImpl $processEngineConfiguration): void
    {
        $idGenerator = $processEngineConfiguration->getIdGenerator();
        if ($idGenerator instanceof DbIdGenerator) {
            $idGenerator->reset();
        }
    }

    /*private static class InteruptTask extends TimerTask {
        protected boolean timeLimitExceeded = false;
        protected Thread thread;
        public InteruptTask(Thread thread) {
            this.thread = thread;
        }
        public boolean isTimeLimitExceeded() {
            return timeLimitExceeded;
        }
        @Override
        public void run() {
            timeLimitExceeded = true;
            thread.interrupt();
        }
    }*/

    public static function getProcessEngine(?string $configurationResource): ProcessEngineInterface
    {
        if (!array_key_exists($configurationResource, self::$processEngines)) {
            $processEngine = ProcessEngineConfiguration::createProcessEngineConfigurationFromResource($configurationResource)
            ->buildProcessEngine();
            self::$processEngines[$configurationResource] = $processEngine;
        } else {
            $processEngine = self::$processEngines[$configurationResource];
        }
        return $processEngine;
    }

    public static function closeProcessEngines(): void
    {
        foreach (self::$processEngines as $processEngine) {
            $processEngine->close();
        }
        self::$processEngines = [];
    }

    public static function createSchema(ProcessEngineConfigurationImpl $processEngineConfiguration): void
    {
        $processEngineConfiguration->getCommandExecutorTxRequired()
        ->execute(new class () implements CommandInterface {
            public function execute(CommandContext $commandContext, ...$args)
            {
                $commandContext->getSession(PersistenceSessionInterface::class)->dbSchemaCreate();
                return null;
            }

            public function isRetryable(): bool
            {
                return false;
            }
        });
    }

    public static function dropSchema(ProcessEngineConfigurationImpl $processEngineConfiguration): void
    {
        $processEngineConfiguration->getCommandExecutorTxRequired()
        ->execute(new class () implements CommandInterface {
            public function execute(CommandContext $commandContext, ...$args)
            {
                $commandContext->getDbSqlSession()->dbSchemaDrop();
                return null;
            }

            public function isRetryable(): bool
            {
                return false;
            }
        });
    }

    public static function createOrUpdateHistoryLevel(ProcessEngineConfigurationImpl $processEngineConfiguration): void
    {
        $processEngineConfiguration->getCommandExecutorTxRequired()
        ->execute(new class () implements CommandInterface {
            public function execute(CommandContext $commandContext, ...$args)
            {
                $dbEntityManager = $commandContext->getDbEntityManager();
                $historyLevelProperty = $dbEntityManager->selectById(PropertyEntity::class, "historyLevel");
                if ($historyLevelProperty != null) {
                    if ($processEngineConfiguration->getHistoryLevel()->getId() != intval($historyLevelProperty->getValue())) {
                        $historyLevelProperty->setValue(strval($processEngineConfiguration->getHistoryLevel()->getId()));
                        $dbEntityManager->merge($historyLevelProperty);
                    }
                } else {
                    HistoryLevelSetupCommand::dbCreateHistoryLevel($commandContext);
                }
                return null;
            }

            public function isRetryable(): bool
            {
                return false;
            }
        });
    }

    public static function deleteHistoryLevel(ProcessEngineConfigurationImpl $processEngineConfiguration): void
    {
        $processEngineConfiguration->getCommandExecutorTxRequired()
        ->execute(new class () implements CommandInterface {
            public function execute(CommandContext $commandContext, ...$args)
            {
                $dbEntityManager = $commandContext->getDbEntityManager();
                $property = $dbEntityManager->selectById(PropertyEntity::class, "historyLevel");
                if ($property !== null) {
                    $dbEntityManager->delete($property);
                }
                return null;
            }

            public function isRetryable(): bool
            {
                return false;
            }
        });
    }

    public static function clearUserOperationLog(ProcessEngineConfigurationImpl $processEngineConfiguration): void
    {
        if ($processEngineConfiguration->getHistoryLevel() == HistoryLevel::historyLevelFull()) {
            $historyService = $processEngineConfiguration->getHistoryService();
            $logs = $historyService->createUserOperationLogQuery()->list();
            foreach ($logs as $log) {
                $historyService->deleteUserOperationLogEntry($log->getId());
            }
        }
    }

    /*public static void deleteTelemetryProperty(ProcessEngineConfigurationImpl processEngineConfiguration) {
        processEngineConfiguration.getCommandExecutorTxRequired()
            .execute(commandContext -> {
            DbEntityManager dbEntityManager = commandContext.getDbEntityManager();
            PropertyEntity telemetryProperty = dbEntityManager.selectById(PropertyEntity.class, "camunda.telemetry.enabled");
            if (telemetryProperty != null) {
                dbEntityManager.delete(telemetryProperty);
            }
            return null;
        });
    }*/

    public static function deleteInstallationId(ProcessEngineConfigurationImpl $processEngineConfiguration): void
    {
        $processEngineConfiguration->getCommandExecutorTxRequired()
        ->execute(new class () implements CommandInterface {
            public function execute(CommandContext $commandContext, ...$args)
            {
                $dbEntityManager = $commandContext->getDbEntityManager();
                $installationIdProperty = $dbEntityManager->selectById(PropertyEntity::class, "engine.installation.id");
                if ($installationIdProperty !== null) {
                    $dbEntityManager->delete($installationIdProperty);
                }
                return null;
            }
        });
    }
    /*public static Object defaultManualActivation() {
        Expression expression = new FixedValue(true);
        CaseControlRuleImpl caseControlRule = new CaseControlRuleImpl(expression);
        return caseControlRule;
    }*/
}
