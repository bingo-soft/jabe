<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Exception\NotFoundException;
use Jabe\Engine\Impl\Interceptor\CommandContext;
use Jabe\Engine\Repository\ResourceDefinitionEntityInterface;
use Jabe\Engine\Impl\Util\EnsureUtil;

class DeleteProcessDefinitionsByKeyCmd extends AbstractDeleteProcessDefinitionCmd
{
    private $processDefinitionKey;
    private $tenantId;
    private $isTenantIdSet;

    public function __construct(string $processDefinitionKey, bool $cascade, bool $skipCustomListeners, bool $skipIoMappings, ?string $tenantId, bool $isTenantIdSet)
    {
        $this->processDefinitionKey = $processDefinitionKey;
        $this->cascade = $cascade;
        $this->skipCustomListeners = $skipCustomListeners;
        $this->skipIoMappings = $skipIoMappings;
        $this->tenantId = $tenantId;
        $this->isTenantIdSet = $isTenantIdSet;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("processDefinitionKey", "processDefinitionKey", $this->processDefinitionKey);

        $processDefinitions = $commandContext->getProcessDefinitionManager()
            ->findDefinitionsByKeyAndTenantId($this->processDefinitionKey, $this->tenantId, $this->isTenantIdSet);
        EnsureUtil::ensureNotEmpty(NotFoundException::class, "processDefinitions", $processDefinitions);

        foreach ($processDefinitions as $processDefinition) {
            $processDefinitionId = $processDefinition->getId();
            $this->deleteProcessDefinitionCmd($commandContext, $processDefinitionId, $this->cascade, $this->skipCustomListeners, $this->skipIoMappings);
        }

        return null;
    }
}
