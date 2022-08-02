<?php

namespace Jabe\Engine\Impl\Repository;

use Jabe\Engine\Impl\Cmd\{
    DeleteProcessDefinitionsByIdsCmd,
    DeleteProcessDefinitionsByKeyCmd
};
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandExecutorInterface
};
use Jabe\Engine\Impl\Util\EnsureUtil;
use Jabe\Engine\Repository\{
    DeleteProcessDefinitionsBuilderInterface,
    DeleteProcessDefinitionsSelectBuilderInterface,
    DeleteProcessDefinitionsTenantBuilderInterface
};

class DeleteProcessDefinitionsBuilderImpl implements DeleteProcessDefinitionsBuilderInterface, DeleteProcessDefinitionsSelectBuilderInterface, DeleteProcessDefinitionsTenantBuilderInterface
{
    private $commandExecutor;

    private $processDefinitionKey;
    private $processDefinitionIds = [];

    private $cascade;
    private $tenantId;
    private $isTenantIdSet;
    private $skipCustomListeners;
    protected $skipIoMappings;

    public function __construct(CommandExecutorInterface $commandExecutor)
    {
        $this->commandExecutor = $commandExecutor;
    }

    public function byIds(array $processDefinitionIds): DeleteProcessDefinitionsBuilderImpl
    {
        if (!empty($processDefinitionIds)) {
            $this->processDefinitionIds = array_merge($this->processDefinitionIds, $processDefinitionIds);
        }
        return $this;
    }

    public function byKey(string $processDefinitionKey): DeleteProcessDefinitionsBuilderImpl
    {
        $this->processDefinitionKey = $processDefinitionKey;
        return $this;
    }

    public function withoutTenantId(): DeleteProcessDefinitionsBuilderImpl
    {
        $this->isTenantIdSet = true;
        return $this;
    }

    public function withTenantId(string $tenantId): DeleteProcessDefinitionsBuilderImpl
    {
        EnsureUtil::ensureNotNull("tenantId", "tenantId", $tenantId);
        $this->isTenantIdSet = true;
        $this->tenantId = $tenantId;
        return $this;
    }

    public function cascade(): DeleteProcessDefinitionsBuilderImpl
    {
        $this->cascade = true;
        return $this;
    }

    public function skipCustomListeners(): DeleteProcessDefinitionsBuilderImpl
    {
        $this->skipCustomListeners = true;
        return $this;
    }

    public function skipIoMappings(): DeleteProcessDefinitionsBuilderImpl
    {
        $this->skipIoMappings = true;
        return $this;
    }

    public function delete(): void
    {
        EnsureUtil::ensureOnlyOneNotNull("'processDefinitionKey' or 'processDefinitionIds' cannot be null", $this->processDefinitionKey, $this->processDefinitionIds);

        $command = null;
        if ($this->processDefinitionKey !== null) {
            $command = new DeleteProcessDefinitionsByKeyCmd($this->processDefinitionKey, $this->cascade, $this->skipCustomListeners, $this->skipIoMappings, $this->tenantId, $this->isTenantIdSet);
        } elseif (!empty($this->processDefinitionIds)) {
            $command = new DeleteProcessDefinitionsByIdsCmd($this->processDefinitionIds, $this->cascade, $this->skipCustomListeners, $this->skipIoMappings);
        } else {
            return;
        }

        $this->commandExecutor->execute($command);
    }
}
