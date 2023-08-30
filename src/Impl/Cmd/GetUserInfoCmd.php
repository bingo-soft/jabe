<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetUserInfoCmd implements CommandInterface
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
        $identityInfo = $commandContext
            ->getIdentityInfoManager()
            ->findUserInfoByUserIdAndKey($this->userId, $this->key);

        return ($identityInfo !== null ? $identityInfo->getValue() : null);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
