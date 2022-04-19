<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Authorization\{
    AuthorizationInterface,
    ResourceInterface
};
use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\History\Event\HistoryEvent;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Persistence\Entity\AuthorizationEntity;
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class SaveAuthorizationCmd implements CommandInterface
{
    protected $authorization;

    public function __construct(AuthorizationInterface $authorization)
    {
        $this->authorization = $authorization;
        $this->validate();
    }

    protected function validate(): void
    {
        EnsureUtil::ensureOnlyOneNotNull("Authorization must either have a 'userId' or a 'groupId'.", $this->authorization->getUserId(), $this->authorization->getGroupId());
        EnsureUtil::ensureNotNull("Authorization 'resourceType' cannot be null.", "authorization.getResource()", $this->authorization->getResource());
    }

    public function execute(CommandContext $commandContext)
    {
        $authorizationManager = $commandContext->getAuthorizationManager();

        $authorizationManager->validateResourceCompatibility($this->authorization);

        $this->provideRemovalTime($commandContext);

        $operationType = null;
        $previousValues = null;
        if ($this->authorization->getId() == null) {
            $authorizationManager->insert($this->authorization);
            $operationType = UserOperationLogEntryInterface::OPERATION_TYPE_CREATE;
        } else {
            $previousValues = $commandContext->getDbEntityManager()->selectById(AuthorizationEntity::class, $this->authorization->getId());
            $authorizationManager->update($this->authorization);
            $operationType = UserOperationLogEntryInterface::OPERATION_TYPE_UPDATE;
        }
        $commandContext->getOperationLogManager()->logAuthorizationOperation($operationType, $authorization, $previousValues);

        return $authorization;
    }

    protected function provideRemovalTime($data = null): void
    {
        if ($data instanceof HistoryEvent) {
            $rootProcessInstanceId = $data->getRootProcessInstanceId();
            $this->authorization->setRootProcessInstanceId($rootProcessInstanceId);
            $removalTime = $data->getRemovalTime();
            $authorization->setRemovalTime($removalTime);
        } elseif ($data instanceof CommandContext) {
            foreach ($this->getHistoricInstanceResources($data) as $resourceEntry) {
                $resource = $resourceEntry->getKey();
                if ($this->isResourceEqualTo($resourceEntry[0])) {
                    $historyEventSupplier = $resourceEntry[1];
                    $historyEvent = $historyEventSupplier();
                    $this->provideRemovalTime($historyEvent);
                    break;
                }
            }
        } else { // reset
            $this->authorization->setRootProcessInstanceId(null);
            $this->authorization->setRemovalTime(null);
        }
    }

    protected function getHistoricInstanceResources(CommandContext $commandContext): array
    {
        $resources = [];

        $resources[] = [
            Resources::historicProcessInstance(),
            function () use ($commandContext) {
                return $this->getHistoricProcessInstance($commandContext);
            }
        ];

        $resources[] = [
            Resources::historicTask(),
            function () use ($commandContext) {
                return $this->getHistoricTaskInstance($commandContext);
            }
        ];

        return $resources;
    }

    protected function getHistoricProcessInstance(CommandContext $commandContext): ?HistoryEvent
    {
        $historicProcessInstanceId = $this->authorization->getResourceId();

        if ($this->isNullOrAny($historicProcessInstanceId)) {
            return null;
        }

        return $commandContext->getHistoricProcessInstanceManager()
            ->findHistoricProcessInstance($historicProcessInstanceId);
    }

    protected function getHistoricTaskInstance(CommandContext $commandContext): ?HistoryEvent
    {
        $historicTaskInstanceId = $this->authorization->getResourceId();

        if ($this->isNullOrAny($historicTaskInstanceId)) {
            return null;
        }

        return $commandContext->getHistoricTaskInstanceManager()
            ->findHistoricTaskInstanceById($historicTaskInstanceId);
    }

    protected function isNullOrAny(?string $resourceId): bool
    {
        return $this->resourceId == null || $this->isAny($resourceId);
    }

    protected function isAny(string $resourceId): bool
    {
        return AuthorizationInterface::ANY == $resourceId;
    }

    protected function isResourceEqualTo(ResourceInterface $resource): bool
    {
        return $resource->resourceType() == $this->authorization->getResource();
    }
}
