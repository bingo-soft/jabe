<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class CheckPassword implements CommandInterface
{
    private $userId;
    private $password;

    public function __construct(?string $userId, ?string $password)
    {
        $this->userId = $userId;
        $this->password = $password;
    }

    public function __serialize(): array
    {
        return [
            'userId' => $this->userId,
            'password' => $this->password
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->userId = $data['userId'];
        $this->password = $data['password'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        return $commandContext->getReadOnlyIdentityProvider()->checkPassword($this->userId, $this->password);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
