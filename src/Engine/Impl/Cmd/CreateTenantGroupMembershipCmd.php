<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\History\UserOperationLogEntryInterface;
use BpmPlatform\Engine\Impl\Identity\IdentityOperationResult;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class CreateTenantGroupMembershipCmd extends AbstractWritableIdentityServiceCmd implements CommandInterface, \Serializable
{
    protected $tenantId;
    protected $groupId;

    public function __construct(string $tenantId, string $groupId)
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
        EnsureUtil::ensureNotNull("tenantId", $this->tenantId);
        EnsureUtil::ensureNotNull("groupId", $this->groupId);

        $operationResult = $commandContext
            ->getWritableIdentityProvider()
            ->createTenantGroupMembership($this->tenantId, $this->groupId);

        $commandContext->getOperationLogManager()->logMembershipOperation($operationResult, null, $this->groupId, $this->tenantId);

        return null;
    }
}
