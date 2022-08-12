<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class DeleteTenantGroupMembershipCmd extends AbstractWritableIdentityServiceCmd implements CommandInterface, \Serializable
{
    protected $tenantId;
    protected $groupId;

    public function __construct(?string $tenantId, ?string $groupId)
    {
        $this->tenantId = $tenantId;
        $this->groupId = $groupId;
    }

    public function serialize()
    {
        return json_encode([
            'tenantId' => $this->tenantId,
            'groupId' => $this->groupId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->tenantId = $json->tenantId;
        $this->groupId = $json->groupId;
    }

    protected function executeCmd(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("tenantId", "tenantId", $this->tenantId);
        EnsureUtil::ensureNotNull("groupId", "groupId", $this->groupId);

        $operationResult = $commandContext
            ->getWritableIdentityProvider()
            ->deleteTenantGroupMembership($this->tenantId, $this->groupId);

        $commandContext->getOperationLogManager()->logMembershipOperation($operationResult, null, $this->groupId, $this->tenantId);

        return null;
    }
}
