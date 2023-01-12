<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class CreateTenantUserMembershipCmd extends AbstractWritableIdentityServiceCmd implements CommandInterface, \Serializable
{
    protected $tenantId;
    protected $userId;

    public function __construct(?string $tenantId, ?string $userId)
    {
        $this->tenantId = $tenantId;
        $this->userId = $userId;
    }

    public function serialize()
    {
        return json_encode([
            'tenantId' => $this->tenantId,
            'userId' => $this->userId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->tenantId = $json->tenantId;
        $this->userId = $json->userId;
    }

    protected function executeCmd(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("tenantId", "tenantId", $this->tenantId);
        EnsureUtil::ensureNotNull("userId", "userId", $this->userId);

        $operationResult = $commandContext
            ->getWritableIdentityProvider()
            ->createTenantUserMembership($this->tenantId, $this->userId);

        $commandContext->getOperationLogManager()->logMembershipOperation($operationResult, $this->userId, null, $this->tenantId);

        return null;
    }
}
