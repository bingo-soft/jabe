<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetUserInfoKeysCmd implements CommandInterface, \Serializable
{
    protected $userId;
    protected $userInfoType;

    public function __construct(?string $userId, ?string $userInfoType)
    {
        $this->userId = $userId;
        $this->userInfoType = $userInfoType;
    }

    public function serialize()
    {
        return json_encode([
            'userId' => $this->userId,
            'userInfoType' => $this->userInfoType
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->userId = $json->userId;
        $this->userInfoType = $json->userInfoType;
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
