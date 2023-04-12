<?php

namespace Jabe\Impl\Cmd;

use Jabe\Exception\NotFoundException;
use Jabe\Impl\Interceptor\CommandContext;
use Jabe\Impl\Util\EnsureUtil;

class DeleteProcessDefinitionsByKeyCmd extends AbstractDeleteProcessDefinitionCmd
{
    private $processDefinitionKey;
    private $tenantId;
    private bool $isTenantIdSet = false;

    public function __construct(?string $processDefinitionKey, bool $cascade, bool $skipCustomListeners, bool $skipIoMappings, ?string $tenantId, bool $isTenantIdSet)
    {
        $this->processDefinitionKey = $processDefinitionKey;
        $this->cascade = $cascade;
        $this->skipCustomListeners = $skipCustomListeners;
        $this->skipIoMappings = $skipIoMappings;
        $this->tenantId = $tenantId;
        $this->isTenantIdSet = $isTenantIdSet;
    }

    public function execute(CommandContext $commandContext, ...$args)
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
