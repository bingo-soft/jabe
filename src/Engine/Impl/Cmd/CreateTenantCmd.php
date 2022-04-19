<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Identity\TenantInterface;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class CreateTenantCmd extends AbstractWritableIdentityServiceCmd implements CommandInterface, \Serializable
{
    protected $tenantId;

    public function __construct(string $tenantId)
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
        return $commandContext
            ->getWritableIdentityProvider()
            ->createNewTenant($this->tenantId);
    }
}
