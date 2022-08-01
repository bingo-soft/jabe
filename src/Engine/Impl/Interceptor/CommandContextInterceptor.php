<?php

namespace Jabe\Engine\Impl\Interceptor;

use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Engine\Impl\Cmd\CommandLogger;
use Jabe\Engine\Impl\Context\{
    Context,
    ProcessEngineContextImpl
};

class CommandContextInterceptor extends CommandInterceptor
{
    //private final static CommandLogger LOG = CommandLogger.CMD_LOGGER;

    protected $commandContextFactory;
    protected $processEngineConfiguration;

    /** if true, we will always open a new command context */
    protected $alwaysOpenNew;

    public function __construct(?CommandContextFactory $commandContextFactory = null, ?ProcessEngineConfigurationImpl $processEngineConfiguration = null, ?bool $alwaysOpenNew = false)
    {
        $this->commandContextFactory = $commandContextFactory;
        $this->processEngineConfiguration = $processEngineConfiguration;
        $this->alwaysOpenNew = $alwaysOpenNew;
    }

    public function execute(CommandInterface $command)
    {
        $context = null;

        if (!$this->alwaysOpenNew) {
            // check whether we can reuse the command context
            $existingCommandContext = Context::getCommandContext();
            if ($existingCommandContext !== null && $this->isFromSameEngine($existingCommandContext)) {
                $context = $existingCommandContext;
            }
        }

        // only create a new command context on the current command level (CAM-10002)
        $isNew = ProcessEngineContextImpl::consume();
        $openNew = ($context === null || $isNew);

        $commandInvocationContext = new CommandInvocationContext($command, $this->processEngineConfiguration);
        Context::setCommandInvocationContext($commandInvocationContext);

        try {
            if ($openNew) {
                //LOG.debugOpeningNewCommandContext();
                $context = $commandContextFactory->createCommandContext();
            } else {
                //LOG.debugReusingExistingCommandContext();
            }

            Context::setCommandContext($context);
            Context::setProcessEngineConfiguration($processEngineConfiguration);

            // delegate to next interceptor in chain
            return $next->execute($command);
        } catch (\Throwable $t) {
            $commandInvocationContext->trySetThrowable($t);
        } finally {
            try {
                if ($openNew) {
                    //LOG.closingCommandContext();
                    $context->close($commandInvocationContext);
                } else {
                    $commandInvocationContext->rethrow();
                }
            } finally {
                Context::removeCommandInvocationContext();
                Context::removeCommandContext();
                Context::removeProcessEngineConfiguration();

                // restore the new command context flag
                ProcessEngineContextImpl::set($isNew);
            }
        }

        return null;
    }

    protected function isFromSameEngine(CommandContext $existingCommandContext): bool
    {
        return $this->processEngineConfiguration == $existingCommandContext->getProcessEngineConfiguration();
    }

    public function getCommandContextFactory(): CommandContextFactory
    {
        return $this->commandContextFactory;
    }

    public function setCommandContextFactory(CommandContextFactory $commandContextFactory): void
    {
        $this->commandContextFactory = $commandContextFactory;
    }

    public function getProcessEngineConfiguration(): ProcessEngineConfigurationImpl
    {
        return $this->processEngineConfiguration;
    }

    public function setProcessEngineContext(ProcessEngineConfigurationImpl $processEngineContext): void
    {
        $this->processEngineConfiguration = $processEngineContext;
    }
}
