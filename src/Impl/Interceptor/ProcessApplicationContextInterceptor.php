<?php

namespace Jabe\Impl\Interceptor;

use Jabe\Application\ProcessApplicationReferenceInterface;
use Jabe\Application\Impl\{
    ProcessApplicationContextImpl,
    ProcessApplicationIdentifier
};
use Jabe\Container\RuntimeContainerDelegate;
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Impl\Cmd\CommandLogger;
use Jabe\Impl\Context\Context;

class ProcessApplicationContextInterceptor extends CommandInterceptor
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    protected $processEngineConfiguration;

    public function __construct(ProcessEngineConfigurationImpl $processEngineConfiguration)
    {
        $this->processEngineConfiguration = $processEngineConfiguration;
    }

    public function execute(CommandInterface $command, ...$args)
    {
        $processApplicationIdentifier = ProcessApplicationContextImpl::get();
        if (empty($args) && !empty($this->getState())) {
            $args = $this->getState();
        }
        if ($processApplicationIdentifier !== null) {
            // clear the identifier so this interceptor does not apply to nested commands
            ProcessApplicationContextImpl::clear();

            try {
                $reference = $this->getPaReference($processApplicationIdentifier);
                $scope = $this;
                return Context::executeWithinProcessApplication(
                    function () use ($scope, $command, $args) {
                        return $scope->next->execute($command, ...$args);
                    },
                    $reference
                );
            } finally {
                // restore the identifier for subsequent commands
                ProcessApplicationContextImpl::set($processApplicationIdentifier);
            }
        } else {
            return $this->next->execute($command, ...$args);
        }
    }

    protected function getPaReference(ProcessApplicationIdentifier $processApplicationIdentifier): ?ProcessApplicationReferenceInterface
    {
        if ($processApplicationIdentifier->getReference() !== null) {
            return $processApplicationIdentifier->getReference();
        } elseif ($processApplicationIdentifier->getProcessApplication() !== null) {
            return $processApplicationIdentifier->getProcessApplication()->getReference();
        } elseif ($processApplicationIdentifier->getName() !== null) {
            $runtimeContainerDelegate = RuntimeContainerDelegate::instance()->get();
            $reference = $runtimeContainerDelegate->getDeployedProcessApplication($processApplicationIdentifier->getName());

            if ($reference === null) {
                //throw LOG.paWithNameNotRegistered(processApplicationIdentifier.getName());
            } else {
                return $reference;
            }
        } else {
            //throw LOG.cannotReolvePa(processApplicationIdentifier);
        }
    }
}
