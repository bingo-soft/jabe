<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class UnlockUserCmd implements CommandInterface, \Serializable
{
    private $userId;

    public function __construct(?string $userId)
    {
        $this->userId = $userId;
    }

    public function serialize()
    {
        return json_encode([
            'userId' => $this->userId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->userId = $json->userId;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $commandContext->getAuthorizationManager()->checkAdmin();

        $operationResult = $commandContext->getWritableIdentityProvider()->unlockUser($this->userId);

        $commandContext->getOperationLogManager()->logUserOperation($operationResult, $this->userId);
        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
