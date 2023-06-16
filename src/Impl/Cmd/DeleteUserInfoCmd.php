<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class DeleteUserInfoCmd implements CommandInterface
{
    protected $userId;
    protected $key;

    public function __construct(?string $userId, ?string $key)
    {
        $this->userId = $userId;
        $this->key = $key;
    }

    public function __serialize(): array
    {
        return [
            'userId' => $this->userId,
            'key' => $this->key
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->userId = $data['userId'];
        $this->key = $data['key'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $commandContext
            ->getIdentityInfoManager()
            ->deleteUserInfoByUserIdAndKey($this->userId, $this->key);
        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
