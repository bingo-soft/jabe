<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    ProcessDefinitionManager,
    PropertyChange,
    UserOperationLogManager
};
use Jabe\Impl\Util\EnsureUtil;

abstract class AbstractDeleteProcessDefinitionCmd implements CommandInterface
{
    protected bool $cascade = false;
    protected bool $skipCustomListeners = false;
    protected bool $skipIoMappings = false;

    public function __serialize(): array
    {
        return [
            'cascade' => $this->cascade,
            'skipCustomListeners' => $this->skipCustomListeners,
            'skipIoMappings' => $this->skipIoMappings
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->cascade = $data['cascade'];
        $this->skipCustomListeners = $data['skipCustomListeners'];
        $this->skipIoMappings = $data['skipIoMappings'];
    }

    protected function deleteProcessDefinitionCmd(CommandContext $commandContext, ?string $processDefinitionId, bool $cascade, bool $skipCustomListeners, bool $skipIoMappings): void
    {
        EnsureUtil::ensureNotNull("processDefinitionId", "processDefinitionId", $processDefinitionId);

        $processDefinition = $commandContext->getProcessDefinitionManager()
            ->findLatestProcessDefinitionById($processDefinitionId);
        EnsureUtil::ensureNotNull(
            "No process definition found with id '" . $processDefinitionId . "'",
            "processDefinition",
            $processDefinition
        );

        $commandCheckers = $commandContext->getProcessEngineConfiguration()->getCommandCheckers();
        foreach ($commandCheckers as $checker) {
            $checker->checkDeleteProcessDefinitionById($processDefinitionId);
        }

        $userOperationLogManager = $commandContext->getOperationLogManager();
        $userOperationLogManager->logProcessDefinitionOperation(
            UserOperationLogEntryInterface::OPERATION_TYPE_DELETE,
            $processDefinitionId,
            $processDefinition->getKey(),
            new PropertyChange("cascade", false, $cascade)
        );

        $definitionManager = $commandContext->getProcessDefinitionManager();
        $definitionManager->deleteProcessDefinition($processDefinition, $processDefinitionId, $cascade, $cascade, $this->skipCustomListeners, $this->skipIoMappings);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
