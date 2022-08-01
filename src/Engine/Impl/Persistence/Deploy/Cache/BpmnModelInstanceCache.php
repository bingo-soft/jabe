<?php

namespace Jabe\Engine\Impl\Persistence\Deploy\Cache;

use Jabe\Engine\Impl\ProcessDefinitionQueryImpl;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Model\Bpmn\{
    Bpmn,
    BpmnModelInstanceInterface
};

class BpmnModelInstanceCache extends ModelInstanceCache
{
    public function __construct(CacheFactoryInterface $factory, int $cacheCapacity, ResourceDefinitionCache $definitionCache)
    {
        parent::__construct($factory, $cacheCapacity, $definitionCache);
    }

    protected function throwLoadModelException(string $definitionId, \Exception $e): void
    {
        //throw LOG.loadModelException("BPMN", "process", definitionId, e);
        throw new \Exception("loadModelException");
    }

    protected function readModelFromStream($bpmnResourceInputStream): BpmnModelInstanceInterface
    {
        return Bpmn::readModelFromStream($bpmnResourceInputStream);
    }

    protected function logRemoveEntryFromDeploymentCacheFailure(string $definitionId, \Exception $e): void
    {
        //LOG.removeEntryFromDeploymentCacheFailure("process", definitionId, e);
    }

    protected function getAllDefinitionsForDeployment(string $deploymentId): array
    {
        $commandContext = Context::getCommandContext();
        $allDefinitionsForDeployment = $commandContext->runWithoutAuthorization(function () use ($deploymentId) {
            return (new ProcessDefinitionQueryImpl())
                ->deploymentId($deploymentId)
                ->list();
        });
        return $allDefinitionsForDeployment;
    }
}
