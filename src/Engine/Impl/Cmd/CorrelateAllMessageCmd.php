<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\MessageCorrelationBuilderImpl;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Runtime\{
    CorrelationHandlerInterface,
    CorrelationSet,
    MessageCorrelationResultImpl,
    CorrelationHandlerResult
};
use Jabe\Engine\Impl\Util\EnsureUtil;

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

    public function execute(CommandContext $commandContext)
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
}
