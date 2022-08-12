<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetUserAccountCmd implements CommandInterface, \Serializable
{
    protected $userId;
    protected $userPassword;
    protected $accountName;

    public function __construct(string $userId, string $userPassword, string $accountName)
    {
        $this->userId = $userId;
        $this->userPassword = $userPassword;
        $this->accountName = $accountName;
    }

    public function serialize()
    {
        return json_encode([
            'userId' => $this->userId,
            'userPassword' => $this->userPassword,
            'accountName' => $this->accountName
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->userId = $json->userId;
        $this->userPassword = $json->userPassword;
        $this->accountName = $json->accountName;
    }

    public function execute(CommandContext $commandContext)
    {
        return $commandContext
            ->getIdentityInfoManager()
            ->findUserAccountByUserIdAndKey($this->userId, $this->userPassword, $this->accountName);
    }
}
