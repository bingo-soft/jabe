<?php

namespace Jabe\Impl\Cmd;

use Jabe\MismatchingMessageCorrelationException;
use Jabe\Impl\MessageCorrelationBuilderImpl;
use Jabe\Impl\ProcessEngineLogger;
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

class CorrelateMessageCmd extends AbstractCorrelateMessageCmd implements CommandInterface
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    protected $startMessageOnly;

    /**
     * Initialize the command with a builder
     *
     * @param messageCorrelationBuilderImpl
     */
    public function __construct(MessageCorrelationBuilderImpl $messageCorrelationBuilderImpl, bool $collectVariables, bool $deserializeVariableValues, bool $startMessageOnly)
    {
        parent::__construct($messageCorrelationBuilderImpl, $collectVariables, $deserializeVariableValues);
        $this->startMessageOnly = $startMessageOnly;
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

        $correlationResult = null;
        $scope = $this;
        if ($this->startMessageOnly) {
            $correlationResults = $commandContext->runWithoutAuthorization(function () use ($scope, $commandContext, $correlationHandler, $correlationSet) {
                return $correlationHandler->correlateStartMessages($commandContext, $scope->messageName, $correlationSet);
            });
            if (empty($correlationResults)) {
                throw new MismatchingMessageCorrelationException($this->messageName, "No process definition matches the parameters");
            } elseif (count($correlationResults) > 1) {
                //throw LOG.exceptionCorrelateMessageToSingleProcessDefinition(messageName, correlationResults.size(), correlationSet);
                throw new \Exception("exceptionCorrelateMessageToSingleProcessDefinition");
            } else {
                $correlationResult = $correlationResults[0];
            }
        } else {
            $correlationResult = $commandContext->runWithoutAuthorization(function () use ($scope, $commandContext, $correlationHandler, $correlationSet) {
                return $correlationHandler->correlateMessage($commandContext, $scope->messageName, $correlationSet);
            });

            if ($correlationResult === null) {
                throw new MismatchingMessageCorrelationException("No process definition or execution matches the parameters");
            }
        }

        // check authorization
        $this->checkAuthorization($correlationResult);

        return $this->createMessageCorrelationResult($commandContext, $correlationResult);
    }
}
