<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Identity\TenantInterface;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class SaveTenantCmd extends AbstractWritableIdentityServiceCmd implements CommandInterface, \Serializable
{
    protected $tenant;

    public function __construct(TenantInterface $tenant)
    {
        $this->tenant = $tenant;
    }

    public function serialize()
    {
        return json_encode([
            'tenant' => serialize($this->tenant)
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->tenant = unserialize($json->tenant);
    }

    protected function executeCmd(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("tenant", "tenant", $this->tenant);
        EnsureUtil::ensureWhitelistedResourceId($commandContext, "Tenant", $tenant->getId());

        $operationResult = $commandContext
            ->getWritableIdentityProvider()
            ->saveTenant($tenant);

        $commandContext->getOperationLogManager()->logTenantOperation($operationResult, $tenant->getId());

        return null;
    }
}
