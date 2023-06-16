<?php

namespace Jabe\Impl\Cmd;

use Jabe\Identity\UserInterface;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class CreateUserCmd extends AbstractWritableIdentityServiceCmd implements CommandInterface
{
    protected $userId;

    public function __construct(?string $userId)
    {
        EnsureUtil::ensureNotNull("userId", "userId", $userId);
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

    protected function executeCmd(CommandContext $commandContext): UserInterface
    {
        return $commandContext
            ->getWritableIdentityProvider()
            ->createNewUser($this->userId);
    }
}
