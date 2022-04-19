<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Impl\Event\EventType;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class MessageEventReceivedCmd implements CommandInterface, \Serializable
{
    protected $executionId;
    protected $processVariables = [];
    protected $processVariablesLocal = [];
    protected $messageName;
    protected $exclusive = false;

    public function __construct(?string $messageName, string $executionId, array $processVariables, array $processVariablesLocal = [], bool $exclusive = false)
    {
        $this->executionId = $executionId;
        $this->messageName = $messageName;
        $this->processVariables = $processVariables;
        $this->processVariablesLocal = $processVariablesLocal;
        $this->exclusive = $exclusive;
    }

    public function serialize()
    {
        return json_encode([
            'executionId' => $this->executionId,
            'messageName' => $this->messageName,
            'processVariables' => $this->processVariables,
            'processVariablesLocal' => $this->processVariablesLocal,
            'exclusive' => $this->exclusive
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->executionId = $json->executionId;
        $this->messageName = $json->messageName;
        $this->processVariables = $json->processVariables;
        $this->processVariables = $json->processVariables;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("executionId", "executionId", $this->executionId);

        $eventSubscriptionManager = $commandContext->getEventSubscriptionManager();
        $eventSubscriptions = [];
        if (!empty($this->messageName)) {
            $eventSubscriptions = $eventSubscriptionManager->findEventSubscriptionsByNameAndExecution(
                EventType::MESSAGE->name(),
                $this->messageName,
                $this->executionId,
                $exclusive
            );
        } else {
            $eventSubscriptions = $eventSubscriptionManager->findEventSubscriptionsByExecutionAndType(
                $this->executionId,
                EventType::MESSAGE->name(),
                $this->exclusive
            );
        }

        EnsureUtil::ensureNotEmpty("Execution with id '" . $this->executionId + "' does not have a subscription to a message event with name '" . $this->messageName . "'", "eventSubscriptions", $eventSubscriptions);
        EnsureUtil::ensureNumberOfElements("More than one matching message subscription found for execution " . $this->executionId, "eventSubscriptions", $eventSubscriptions, 1);

        // there can be only one:
        $eventSubscriptionEntity = $eventSubscriptions[0];

        // check authorization
        $processInstanceId = $eventSubscriptionEntity->getProcessInstanceId();
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkUpdateProcessInstanceById($processInstanceId);
        }

        $eventSubscriptionEntity->eventReceived($this->processVariables, $this->processVariablesLocal, null, false);

        return null;
    }
}
