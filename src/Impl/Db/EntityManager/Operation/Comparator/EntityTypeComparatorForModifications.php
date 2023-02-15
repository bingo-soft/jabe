<?php

namespace Jabe\Impl\Db\EntityManager\Operation\Comparator;

use Jabe\Batch\BatchInterface;
use Jabe\Impl\Persistence\Entity\{
    IncidentEntity,
    VariableInstanceEntity,
    IdentityLinkEntity,
    EventSubscriptionEntity,
    JobEntity,
    MessageEntity,
    TimerEntity,
    EverLivingJobEntity,
    MembershipEntity,
    TenantMembershipEntity,
    //CaseSentryPartEntity,
    ExternalTaskEntity,
    TenantEntity,
    GroupEntity,
    UserEntity,
    ByteArrayEntity,
    TaskEntity,
    JobDefinition,
    ExecutionEntity,
    //CaseExecutionEntity,
    ProcessDefinitionEntity,
    //CaseDefinitionEntity,
    //DecisionDefinitionEntity,
    //DecisionRequirementsDefinitionEntity,
    ResourceEntity,
    // 5
    DeploymentEntity
};

class EntityTypeComparatorForModifications implements ComparatorInterface
{
    public const TYPE_ORDER = [
        IncidentEntity::class => 1,
        VariableInstanceEntity::class => 1,
        IdentityLinkEntity::class => 1,
        EventSubscriptionEntity::class => 1,
        JobEntity::class => 1,
        MessageEntity::class => 1,
        TimerEntity::class => 1,
        EverLivingJobEntity::class => 1,
        MembershipEntity::class => 1,
        TenantMembershipEntity::class => 1,
        //CaseSentryPartEntity,
        ExternalTaskEntity::class => 1,
        BatchInterface::class => 1,
        TenantEntity::class => 2,
        GroupEntity::class => 2,
        UserEntity::class => 2,
        ByteArrayEntity::class => 2,
        TaskEntity::class => 2,
        JobDefinition::class => 2,
        ExecutionEntity::class => 3,
        //CaseExecutionEntity,
        ProcessDefinitionEntity::class => 4,
        //CaseDefinitionEntity,
        //DecisionDefinitionEntity,
        //DecisionRequirementsDefinitionEntity,
        ResourceEntity::class => 4,
        // 5
        DeploymentEntity::class => 5
    ];

    public function compareTo(/*string*/$firstEntityType, /*string*/$secondEntityType): int
    {
        if ($firstEntityType == $secondEntityType) {
            $result = 0;
        }

        $firstIndex = null;
        $secondIndex = null;

        if (array_key_exists($firstEntityType, self::TYPE_ORDER)) {
            $firstIndex = self::TYPE_ORDER[$firstEntityType];
        }
        if (array_key_exists($secondEntityType, self::TYPE_ORDER)) {
            $secondIndex = self::TYPE_ORDER[$secondEntityType];
        }

        // unknown type happens before / after everything else
        if ($firstIndex === null) {
            $firstIndex = PHP_INT_MAX;
        }
        if ($secondIndex === null) {
            $secondIndex = PHP_INT_MAX;
        }

        if ($firstIndex == $secondIndex) {
            if ($firstEntityType > $secondEntityType) {
                $result = 1;
            } else {
                $result = -1;
            }
        } elseif ($firstIndex < $secondIndex) {
            $result = -1;
        } else {
            $result = 1;
        }

        return $result;
    }
}
