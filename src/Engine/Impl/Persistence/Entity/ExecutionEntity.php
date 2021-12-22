<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\{
    ProcessEngineInterface,
    ProcessEngineServicesInterface
};
use BpmPlatform\Engine\Delegate\ExecutionListenerInterface;
use BpmPlatform\Engine\Impl\ProcessEngineLogger;
use BpmPlatform\Engine\Impl\Bpmn\Parser\{
    BpmnParse,
    EventSubscriptionDeclaration
};
use BpmPlatform\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use BpmPlatform\Engine\Impl\Cfg\Multitenancy\{
    TenantIdProviderInterface,
    TenantIdProviderProcessInstanceContext
};
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Core\Instance\CoreExecution;
use BpmPlatform\Engine\Impl\Core\Operation\CoreAtomicOperationInterface;
use BpmPlatform\Engine\Impl\Core\Variable\CoreVariableInstanceInterface;
use BpmPlatform\Engine\Impl\Core\Variable\Event\VariableEvent;
use BpmPlatform\Engine\Impl\Core\Variable\Scope\{
    VariableCollectionProvider,
    VariableInstanceFactoryInterface,
    VariableInstanceLifecycleListenerInterface,
    VariableListenerInvocationListener,
    VariableStore,
    VariablesProviderInterface
};
use BpmPlatform\Engine\Impl\Db\{
    DbEntityInterface,
    EnginePersistenceLogger,
    HasDbReferencesInterface,
    HasDbRevisionInterface,
};
use BpmPlatform\Engine\Impl\Event\EventType;
use BpmPlatform\Engine\Impl\History\AbstractHistoryLevel;
use BpmPlatform\Engine\Impl\History\Event\{
    HistoricVariableUpdateEventEntity,
    HistoryEvent,
    HistoryEventProcessor,
    HistoryEventTypes
};
use BpmPlatform\Engine\Impl\History\Producer\HistoryEventProducerInterface;
use BpmPlatform\Engine\Impl\Incident\{
    IncidentContext,
    IncidentHandling
};
