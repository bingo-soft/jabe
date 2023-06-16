<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class DeleteTenantCmd extends AbstractWritableIdentityServiceCmd implements CommandInterface
{
    protected $tenantId;

    public function __construct(?string $tenantId)
    {
        EnsureUtil::ensureNotNull("tenantId", "tenantId", $tenantId);
        $this->tenantId = $tenantId;
    }

    public function __serialize(): array
    {
        return [
            'tenantId' => $this->tenantId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->tenantId = $data['tenantId'];
    }

    protected function executeCmd(CommandContext $commandContext)
    {
        $operationResult = $commandContext
            ->getWritableIdentityProvider()
            ->deleteTenant($this->tenantId);

        $commandContext->getOperationLogManager()->logTenantOperation($operationResult, $this->tenantId);

        return null;
    }
}
