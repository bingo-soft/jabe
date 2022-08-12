<?php

namespace Jabe\Impl\El;

use Jabe\Application\{
    ProcessApplicationInterface,
    ProcessApplicationReferenceInterface,
    ProcessApplicationUnavailableException
};
use Jabe\ProcessEngineException;
use Jabe\Impl\Context\Context;
use El\ELResolver;

class ProcessApplicationElResolverDelegate extends AbstractElResolverDelegate
{
    protected function getElResolverDelegate(): ?ELResolver
    {
        $processApplicationReference = Context::getCurrentProcessApplication();
        if ($processApplicationReference !== null) {
            try {
                $processApplication = $processApplicationReference->getProcessApplication();
                return $processApplication->getElResolver();
            } catch (ProcessApplicationUnavailableException $e) {
                throw new ProcessEngineException("Cannot access process application '" . $processApplicationReference->getName() . "'", $e);
            }
        }
        return null;
    }
}
