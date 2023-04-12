<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\MessageCorrelationBuilderImpl;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Runtime\{
    CorrelationHandlerInterface,
    CorrelationSet,
    MessageCorrelationResultImpl,
    CorrelationHandlerResult
};
use Jabe\Impl\Util\EnsureUtil;

class CorrelateAllMessageCmd extends AbstractCorrelateMessageCmd implements CommandInterface
{
    /**
     * Initialize the command with a builder
     *
     * @param messageCorrelationBuilderImpl
     */
    public function __construct(MessageCorrelationBuilderImpl $messageCorrelationBuilderImpl, bool $collectVariables, bool $deserializeVariableValues)
    {
        parent::__construct($messageCorrelationBuilderImpl, $collectVariables, $deserializeVariableValues);
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        EnsureUtil::ensureAtLeastOneNotNull(
            "At least one of the following correlation criteria has to be present: " .
            "messageName, businessKey, correlationKeys, processInstanceId",
            $this->messageName,
            $this->builder->getBusinessKey(),
            $this->builder->getCorrelationProcessInstanceVariables(),
            $this->builder->getProcessInstanceId()
        );

        $correlationHandler = Context::getProcessEngineConfiguration()->getCorrelationHandler();
        $correlationSet = new CorrelationSet($this->builder);
        $scope = $this;
        $correlationResults = $commandContext->runWithoutAuthorization(function () use ($scope, $commandContext, $correlationHandler, $correlationSet) {
            return $correlationHandler->correlateMessages($commandContext, $scope->messageName, $correlationSet);
        });

        // check authorization
        foreach ($correlationResults as $correlationResult) {
            $this->checkAuthorization($correlationResult);
        }

        $results = [];
        foreach ($correlationResults as $correlationResult) {
            $results[] = $this->createMessageCorrelationResult($commandContext, $correlationResult);
        }

        return $results;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
