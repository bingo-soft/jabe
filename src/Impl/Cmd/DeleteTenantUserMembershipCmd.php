<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class DeleteTenantUserMembershipCmd extends AbstractWritableIdentityServiceCmd implements CommandInterface
{
    protected $tenantId;
    protected $userId;

    public function __construct(?string $tenantId, ?string $userId)
    {
        $this->tenantId = $tenantId;
        $this->userId = $userId;
    }

    public function __serialize(): array
    {
        return [
            'tenantId' => $this->tenantId,
            'userId' => $this->userId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->tenantId = $data['tenantId'];
        $this->userId = $data['userId'];
    }

    protected function executeCmd(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("tenantId", "tenantId", $this->tenantId);
        EnsureUtil::ensureNotNull("userId", "userId", $this->userId);

        $operationResult = $commandContext
            ->getWritableIdentityProvider()
            ->deleteTenantUserMembership($this->tenantId, $this->userId);

        $commandContext->getOperationLogManager()->logMembershipOperation($operationResult, $this->userId, null, $this->tenantId);

        return null;
    }
}
