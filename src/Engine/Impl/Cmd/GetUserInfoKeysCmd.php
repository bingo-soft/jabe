<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetUserInfoKeysCmd implements CommandInterface, \Serializable
{
    protected $userId;
    protected $userInfoType;

    public function __construct(string $userId, string $userInfoType)
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

    public function execute(CommandContext $commandContext)
    {
        return $commandContext
            ->getIdentityInfoManager()
            ->findUserInfoKeysByUserIdAndType($this->userId, $this->userInfoType);
    }
}
