<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetUserInfoKeysCmd implements CommandInterface
{
    protected $userId;
    protected $userInfoType;

    public function __construct(?string $userId, ?string $userInfoType)
    {
        $this->userId = $userId;
        $this->userInfoType = $userInfoType;
    }

    public function __serialize(): array
    {
        return [
            'userId' => $this->userId,
            'userInfoType' => $this->userInfoType
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->userId = $data['userId'];
        $this->userInfoType = $data['userInfoType'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        return $commandContext
            ->getIdentityInfoManager()
            ->findUserInfoKeysByUserIdAndType($this->userId, $this->userInfoType);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
