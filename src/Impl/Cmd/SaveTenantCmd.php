<?php

namespace Jabe\Impl\Cmd;

use Jabe\Identity\TenantInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class SaveTenantCmd extends AbstractWritableIdentityServiceCmd implements CommandInterface
{
    protected $tenant;

    public function __construct(TenantInterface $tenant)
    {
        $this->tenant = $tenant;
    }

    public function __serialize(): array
    {
        return [
            'tenant' => serialize($this->tenant)
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->tenant = unserialize($data['tenant']);
    }

    protected function executeCmd(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("tenant", "tenant", $this->tenant);
        EnsureUtil::ensureWhitelistedResourceId($commandContext, "Tenant", $this->tenant->getId());

        $operationResult = $commandContext
            ->getWritableIdentityProvider()
            ->saveTenant($this->tenant);

        $commandContext->getOperationLogManager()->logTenantOperation($operationResult, $this->tenant->getId());

        return null;
    }
}
