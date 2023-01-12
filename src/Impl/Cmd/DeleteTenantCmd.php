<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class DeleteTenantCmd extends AbstractWritableIdentityServiceCmd implements CommandInterface, \Serializable
{
    protected $tenantId;

    public function __construct(?string $tenantId)
    {
        EnsureUtil::ensureNotNull("tenantId", "tenantId", $tenantId);
        $this->tenantId = $tenantId;
    }

    public function serialize()
    {
        return json_encode([
            'tenantId' => $this->tenantId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->tenantId = $json->tenantId;
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
