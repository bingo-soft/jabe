<?php

namespace Jabe\Engine\Impl\Interceptor;

use Jabe\Engine\Application\ProcessApplicationReferenceInterface;
use Jabe\Engine\Application\Impl\{
    ProcessApplicationContextImpl,
    ProcessApplicationIdentifier
};
use Jabe\Engine\Container\RuntimeContainerDelegate;
use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use Jabe\Engine\Impl\Cmd\CommandLogger;
use Jabe\Engine\Impl\Context\Context;

class ProcessApplicationContextInterceptor extends CommandInterceptor
{
    //private final static CommandLogger LOG = ProcessEngineLogger.CMD_LOGGER;

    protected $processEngineConfiguration;

    public function __construct(ProcessEngineConfigurationImpl $processEngineConfiguration)
    {
        $this->processEngineConfiguration = $processEngineConfiguration;
    }

    public function execute(CommandInterface $command)
    {
        $processApplicationIdentifier = ProcessApplicationContextImpl::get();

        if ($processApplicationIdentifier !== null) {
            // clear the identifier so this interceptor does not apply to nested commands
            ProcessApplicationContextImpl::clear();

            try {
                $reference = $this->getPaReference($processApplicationIdentifier);
                $scope = $this;
                return Context::executeWithinProcessApplication(
                    function () use ($scope, $command) {
                        return $scope->next->execute($command);
                    },
                    $reference
                );
            } finally {
                // restore the identifier for subsequent commands
                ProcessApplicationContextImpl::set($processApplicationIdentifier);
            }
        } else {
            return $this->next->execute($command);
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
