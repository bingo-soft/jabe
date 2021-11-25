<?php

namespace BpmPlatform\Engine\Impl\El;

use BpmPlatform\Engine\Application\{
    ProcessApplicationInterface,
    ProcessApplicationReferenceInterface,
    ProcessApplicationUnavailableException
};
use BpmPlatform\Engine\ProcessEngineException;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Util\El\ELResolver;

class ProcessApplicationElResolverDelegate extends AbstractElResolverDelegate
{
    protected function getElResolverDelegate(): ?ELResolver
    {
        $processApplicationReference = Context::getCurrentProcessApplication();
        if ($processApplicationReference != null) {
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
