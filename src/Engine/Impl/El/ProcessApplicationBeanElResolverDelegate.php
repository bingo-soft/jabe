<?php

namespace BpmPlatform\Engine\Impl\El;

use BpmPlatform\Engine\Application\{
    ProcessApplicationInterface,
    ProcessApplicationReferenceInterface,
    ProcessApplicationUnavailableException
};
use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Util\El\{
    BeanELResolver,
    ELResolver
};

class ProcessApplicationBeanElResolverDelegate extends AbstractElResolverDelegate
{
    protected function getElResolverDelegate(): ?ELResolver
    {
        $processApplicationReference = Context::getCurrentProcessApplication();

        if ($processApplicationReference != null) {
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
