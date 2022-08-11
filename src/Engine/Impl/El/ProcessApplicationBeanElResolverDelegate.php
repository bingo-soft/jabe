<?php

namespace Jabe\Engine\Impl\El;

use Jabe\Engine\Application\{
    ProcessApplicationInterface,
    ProcessApplicationReferenceInterface,
    ProcessApplicationUnavailableException
};
use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Impl\Context\Context;
use El\{
    BeanELResolver,
    ELResolver
};

class ProcessApplicationBeanElResolverDelegate extends AbstractElResolverDelegate
{
    protected function getElResolverDelegate(): ?ELResolver
    {
        $processApplicationReference = Context::getCurrentProcessApplication();

        if ($processApplicationReference !== null) {
            try {
                $processApplication = $processApplicationReference->getProcessApplication();
                return $processApplication->getBeanElResolver();
            } catch (ProcessApplicationUnavailableException $e) {
                throw new ProcessEngineException("Cannot access process application '"  . $processApplicationReference->getName() . "'", $e);
            }
        } else {
            return new BeanELResolver();
        }
    }
}
