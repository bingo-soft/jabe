<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Exception\NotFoundException;
use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\{
    ProcessDefinitionManager,
    PropertyChange,
    UserOperationLogManager
};
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Repository\ProcessDefinitionInterface;

abstract class AbstractDeleteProcessDefinitionCmd implements CommandInterface, \Serializable
{
    protected $cascade;
    protected $skipCustomListeners;
    protected $skipIoMappings;

    public function serialize()
    {
        return json_encode([
            'cascade' => $this->cascade,
            'skipCustomListeners' => $this->skipCustomListeners,
            'skipIoMappings' => $this->skipIoMappings
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->cascade = $json->cascade;
        $this->skipCustomListeners = $json->skipCustomListeners;
        $this->skipIoMappings = $json->skipIoMappings;
    }

    protected function deleteProcessDefinitionCmd(CommandContext $commandContext, string $processDefinitionId, bool $cascade, bool $skipCustomListeners, bool $skipIoMappings): void
    {
        EnsureUtil::ensureNotNull("processDefinitionId", $processDefinitionId);

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
}
