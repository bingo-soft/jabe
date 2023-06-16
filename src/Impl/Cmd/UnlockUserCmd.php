<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class UnlockUserCmd implements CommandInterface
{
    private $userId;

    public function __construct(?string $userId)
    {
        $this->userId = $userId;
    }

    public function __serialize(): array
    {
        return [
            'userId' => $this->userId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->userId = $data['userId'];
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
