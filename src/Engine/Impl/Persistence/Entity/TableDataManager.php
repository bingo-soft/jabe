<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\History\{
    HistoricActivityInstanceInterface,
    HistoricDetailInterface,
    HistoricFormPropertyInterface,
    HistoricProcessInstanceInterface,
    HistoricTaskInstanceInterface,
    HistoricVariableInstanceInterface,
    HistoricVariableUpdateInterface
};
use Jabe\Engine\Impl\{
    ProcessEngineLogger,
    TablePageQueryImpl
};
use Jabe\Engine\Impl\Batch\BatchEntity;
use Jabe\Engine\Impl\Batch\History\HistoricBatchEntity;
use Jabe\Engine\Impl\Db\{
    DbEntityInterface,
    EnginePersistenceLogger
};
use Jabe\Engine\Impl\Db\Sql\DbSqlSessionFactory;
use Jabe\Engine\Impl\History\Event\{
    HistoricDetailEventEntity,
    HistoricExternalTaskLogEntity,
    HistoricIncidentEventEntity,
    UserOperationLogEntryEventEntity
};
use Jabe\Engine\Impl\Persistence\AbstractManager;
use Jabe\Engine\Impl\Util\DatabaseUtil;
use Jabe\Engine\Management\{
    TableMetaData,
    TablePage
};
use Jabe\Engine\Repository\{
    DeploymentInterface,
    ProcessDefinitionInterface
};
use Jabe\Engine\Runtime\{
    ExecutionInterface,
    IncidentInterface,
    JobInterface,
    ProcessInstanceInterface
};

class TableDataManager extends AbstractManager
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    public static $apiTypeToTableNameMap = [];
    public static $persistentObjectToTableNameMap = [];

    public function __construct()
    {
        self::$persistentObjectToTableNameMap[TaskEntity::class] = "ACT_RU_TASK";
        self::$persistentObjectToTableNameMap[ExternalTaskEntity::class] = "ACT_RU_EXT_TASK";
        self::$persistentObjectToTableNameMap[ExecutionEntity::class] = "ACT_RU_EXECUTION";
        self::$persistentObjectToTableNameMap[IdentityLinkEntity::class] = "ACT_RU_IDENTITYLINK";
        self::$persistentObjectToTableNameMap[VariableInstanceEntity::class] = "ACT_RU_VARIABLE";

        self::$persistentObjectToTableNameMap[JobEntity::class] = "ACT_RU_JOB";
        self::$persistentObjectToTableNameMap[MessageEntity::class] = "ACT_RU_JOB";
        self::$persistentObjectToTableNameMap[TimerEntity::class] = "ACT_RU_JOB";
        self::$persistentObjectToTableNameMap[JobDefinitionEntity::class] = "ACT_RU_JOBDEF";
        self::$persistentObjectToTableNameMap[BatchEntity::class] = "ACT_RU_BATCH";

        self::$persistentObjectToTableNameMap[IncidentEntity::class] = "ACT_RU_INCIDENT";

        self::$persistentObjectToTableNameMap[EventSubscriptionEntity::class] = "ACT_RU_EVENT_SUBSCR";


        self::$persistentObjectToTableNameMap[MeterLogEntity::class] = "ACT_RU_METER_LOG";
        self::$persistentObjectToTableNameMap[TaskMeterLogEntity::class] = "ACT_RU_TASK_METER_LOG";

        self::$persistentObjectToTableNameMap[FormDefinitionEntity::class] = "ACT_RE_FORMDEF";
        // repository
        self::$persistentObjectToTableNameMap[DeploymentEntity::class] = "ACT_RE_DEPLOYMENT";
        self::$persistentObjectToTableNameMap[ProcessDefinitionEntity::class] = "ACT_RE_PROCDEF";

        // CMMN
        //self::$persistentObjectToTableNameMap[CaseDefinitionEntity::class] = "ACT_RE_CASE_DEF";
        //self::$persistentObjectToTableNameMap[CaseExecutionEntity::class] = "ACT_RU_CASE_EXECUTION";
        //self::$persistentObjectToTableNameMap[CaseSentryPartEntity::class] = "ACT_RU_CASE_SENTRY_PART";

        // DMN
        //self::$persistentObjectToTableNameMap[DecisionRequirementsDefinitionEntity::class] = "ACT_RE_DECISION_REQ_DEF";
        //self::$persistentObjectToTableNameMap[DecisionDefinitionEntity::class] = "ACT_RE_DECISION_DEF";
        //self::$persistentObjectToTableNameMap[HistoricDecisionInputInstanceEntity::class] = "ACT_HI_DEC_IN";
        //self::$persistentObjectToTableNameMap[HistoricDecisionOutputInstanceEntity::class] = "ACT_HI_DEC_OUT";

        // history
        self::$persistentObjectToTableNameMap[CommentEntity::class] = "ACT_HI_COMMENT";

        self::$persistentObjectToTableNameMap[HistoricActivityInstanceEntity::class] = "ACT_HI_ACTINST";
        self::$persistentObjectToTableNameMap[AttachmentEntity::class] = "ACT_HI_ATTACHMENT";
        self::$persistentObjectToTableNameMap[HistoricProcessInstanceEntity::class] = "ACT_HI_PROCINST";
        self::$persistentObjectToTableNameMap[HistoricTaskInstanceEntity::class] = "ACT_HI_TASKINST";
        self::$persistentObjectToTableNameMap[HistoricJobLogEventEntity::class] = "ACT_HI_JOB_LOG";
        self::$persistentObjectToTableNameMap[HistoricIncidentEventEntity::class] = "ACT_HI_INCIDENT";
        self::$persistentObjectToTableNameMap[HistoricBatchEntity::class] = "ACT_HI_BATCH";
        self::$persistentObjectToTableNameMap[HistoricExternalTaskLogEntity::class] = "ACT_HI_EXT_TASK_LOG";

        //self::$persistentObjectToTableNameMap[HistoricCaseInstanceEntity::class] = "ACT_HI_CASEINST";
        //self::$persistentObjectToTableNameMap[HistoricCaseActivityInstanceEntity::class] = "ACT_HI_CASEACTINST";
        self::$persistentObjectToTableNameMap[HistoricIdentityLinkLogEntity::class] = "ACT_HI_IDENTITYLINK";
        // a couple of stuff goes to the same table
        self::$persistentObjectToTableNameMap[HistoricFormPropertyEntity::class] = "ACT_HI_DETAIL";
        self::$persistentObjectToTableNameMap[HistoricVariableInstanceEntity::class] = "ACT_HI_VARINST";
        self::$persistentObjectToTableNameMap[HistoricDetailEventEntity::class] = "ACT_HI_DETAIL";

        //self::$persistentObjectToTableNameMap[HistoricDecisionInstanceEntity::class] = "ACT_HI_DECINST";
        self::$persistentObjectToTableNameMap[UserOperationLogEntryEventEntity::class] = "ACT_HI_OP_LOG";


        // Identity module
        self::$persistentObjectToTableNameMap[GroupEntity::class] = "ACT_ID_GROUP";
        self::$persistentObjectToTableNameMap[MembershipEntity::class] = "ACT_ID_MEMBERSHIP";
        self::$persistentObjectToTableNameMap[TenantEntity::class] = "ACT_ID_TENANT";
        self::$persistentObjectToTableNameMap[TenantMembershipEntity::class] = "ACT_ID_TENANT_MEMBER";
        self::$persistentObjectToTableNameMap[UserEntity::class] = "ACT_ID_USER";
        self::$persistentObjectToTableNameMap[IdentityInfoEntity::class] = "ACT_ID_INFO";
        self::$persistentObjectToTableNameMap[AuthorizationEntity::class] = "ACT_RU_AUTHORIZATION";


        // general
        self::$persistentObjectToTableNameMap[PropertyEntity::class] = "ACT_GE_PROPERTY";
        self::$persistentObjectToTableNameMap[ByteArrayEntity::class] = "ACT_GE_BYTEARRAY";
        self::$persistentObjectToTableNameMap[ResourceEntity::class] = "ACT_GE_BYTEARRAY";
        self::$persistentObjectToTableNameMap[SchemaLogEntryEntity::class] = "ACT_GE_SCHEMA_LOG";
        self::$persistentObjectToTableNameMap[FilterEntity::class] = "ACT_RU_FILTER";

        // and now the map for the API types (does not cover all cases)
        self::$apiTypeToTableNameMap[Task::class] = "ACT_RU_TASK";
        self::$apiTypeToTableNameMap[Execution::class] = "ACT_RU_EXECUTION";
        self::$apiTypeToTableNameMap[ProcessInstance::class] = "ACT_RU_EXECUTION";
        self::$apiTypeToTableNameMap[ProcessDefinition::class] = "ACT_RE_PROCDEF";
        self::$apiTypeToTableNameMap[Deployment::class] = "ACT_RE_DEPLOYMENT";
        self::$apiTypeToTableNameMap[Job::class] = "ACT_RU_JOB";
        self::$apiTypeToTableNameMap[Incident::class] = "ACT_RU_INCIDENT";
        self::$apiTypeToTableNameMap[Filter::class] = "ACT_RU_FILTER";

        // history
        self::$apiTypeToTableNameMap[HistoricProcessInstance::class] = "ACT_HI_PROCINST";
        self::$apiTypeToTableNameMap[HistoricActivityInstance::class] = "ACT_HI_ACTINST";
        self::$apiTypeToTableNameMap[HistoricDetail::class] = "ACT_HI_DETAIL";
        self::$apiTypeToTableNameMap[HistoricVariableUpdate::class] = "ACT_HI_DETAIL";
        self::$apiTypeToTableNameMap[HistoricFormProperty::class] = "ACT_HI_DETAIL";
        self::$apiTypeToTableNameMap[HistoricTaskInstance::class] = "ACT_HI_TASKINST";
        self::$apiTypeToTableNameMap[HistoricVariableInstance::class] = "ACT_HI_VARINST";

        //self::$apiTypeToTableNameMap[HistoricCaseInstance::class] = "ACT_HI_CASEINST";
        //self::$apiTypeToTableNameMap[HistoricCaseActivityInstance::class] = "ACT_HI_CASEACTINST";

        //self::$apiTypeToTableNameMap[HistoricDecisionInstance::class] = "ACT_HI_DECINST";

        // TODO: Identity skipped for the moment as no SQL injection is provided here
    }

    protected function getTableCount(?string $tableName = null)
    {
        if ($tableName !== null) {
            //LOG.selectTableCountForTable(tableName);
            $count = $this->getDbEntityManager()->selectOne(
                "selectTableCount",
                ["tableName" => $tableName]
            );
            return $count;
        } else {
            $tableCount = [];
            try {
                foreach ($this->getDbEntityManager()->getTableNamesPresentInDatabase() as $tableName) {
                    $tableCount[$tableName] = $this->getTableCount($tableName);
                }
                //LOG.countRowsPerProcessEngineTable(tableCount);
            } catch (\Exception $e) {
                //throw LOG.countTableRowsException(e);
                throw $e;
            }
            return $tableCount;
        }
    }

    public function getTablePage(TablePageQueryImpl $tablePageQuery): TablePage
    {
        $tablePage = new TablePage();

        $tableData = $this->getDbEntityManager()->selectList("selectTableData", $tablePageQuery);

        $tablePage->setTableName($tablePageQuery->getTableName());
        $tablePage->setTotal(getTableCount($tablePageQuery->getTableName()));
        $tablePage->setRows($tableData);
        $tablePage->setFirstResult($tablePageQuery->getFirstResult());

        return $tablePage;
    }

    public function getEntities(string $tableName): array
    {
        $databaseTablePrefix = $this->getDbSqlSession()->getDbSqlSessionFactory()->getDatabaseTablePrefix();
        $entities = [];

        $entityClasses = array_keys(self::$persistentObjectToTableNameMap);
        foreach ($entityClasses as $entityClass) {
            $entityTableName = self::$persistentObjectToTableNameMap[$entityClass];
            if (($databaseTablePrefix . $entityTableName) == $tableName) {
                $entities[] = $entityClass;
            }
        }
        return $entities;
    }

    public function getTableName(string $entityClass, bool $withPrefix): ?string
    {
        $databaseTablePrefix = $this->getDbSqlSession()->getDbSqlSessionFactory()->getDatabaseTablePrefix();
        $tableName = null;

        if (is_a($entityClass, DbEntity::class, true)) {
            $tableName = self::$persistentObjectToTableNameMap[$entityClass];
        } else {
            $tableName = self::$apiTypeToTableNameMap[$entityClass];
        }
        if ($withPrefix) {
            return $databaseTablePrefix . $tableName;
        } else {
            return $tableName;
        }
    }

    public function getTableMetaData(string $tableName): TableMetaData
    {
        $result = new TableMetaData();
        $resultSet = null;

        try {
            try {
                $result->setTableName($tableName);
                $metaData = $this->getDbSqlSession()
                    ->getSqlSession()
                    ->getConnection()
                    ->getMetaData();

                if (DatabaseUtil::checkDatabaseType([DbSqlSessionFactory::POSTGRES])) {
                    $tableName = strtolower($tableName);
                }

                $databaseSchema = $this->getDbSqlSession()->getDbSqlSessionFactory()->getDatabaseSchema();
                $tableName = $this->getDbSqlSession()->prependDatabaseTablePrefix($tableName);

                $resultSet = $metaData->getColumns(null, $databaseSchema, $tableName, null);
                foreach ($resultSet as $res) {
                    //TODO. Test it
                    $name = strtoupper($res["COLUMN_NAME"]);
                    $type = strtoupper($res["TYPE_NAME"]);
                    $result->addColumnMetaData($name, $type);
                }
            } catch (\Exception $se) {
                throw $se;
            } finally {
                if ($resultSet !== null) {
                    //$resultSet->close();
                }
            }
        } catch (\Exception $e) {
            //throw LOG.retrieveMetadataException(e);
            throw $e;
        }

        if (count($result->getColumnNames()) == 0) {
            // According to API, when a table doesn't exist, null should be returned
            $result = null;
        }
        return $result;
    }
}
