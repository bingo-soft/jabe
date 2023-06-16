<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class CreateTenantGroupMembershipCmd extends AbstractWritableIdentityServiceCmd implements CommandInterface
{
    protected $tenantId;
    protected $groupId;

    public function __construct(?string $tenantId, ?string $groupId)
    {
        $this->tenantId = $tenantId;
        $this->groupId = $groupId;
    }

    public function __serialize(): array
    {
        return [
            'tenantId' => $this->tenantId,
            'groupId' => $this->groupId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->tenantId = $data['tenantId'];
        $this->groupId = $data['groupId'];
    }

    protected function executeCmd(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("tenantId", "tenantId", $this->tenantId);
        EnsureUtil::ensureNotNull("groupId", "groupId", $this->groupId);

        $operationResult = $commandContext
            ->getWritableIdentityProvider()
            ->createTenantGroupMembership($this->tenantId, $this->groupId);

        $commandContext->getOperationLogManager()->logMembershipOperation($operationResult, null, $this->groupId, $this->tenantId);

        return null;
    }
}
