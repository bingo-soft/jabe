<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetUserInfoCmd implements CommandInterface, \Serializable
{
    protected $userId;
    protected $key;

    public function __construct(string $userId, string $key)
    {
        $this->userId = $userId;
        $this->key = $key;
    }

    public function serialize()
    {
        return json_encode([
            'userId' => $this->userId,
            'key' => $this->key
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->userId = $json->userId;
        $this->key = $json->key;
    }

    public function execute(CommandContext $commandContext)
    {
        $identityInfo = $commandContext
            ->getIdentityInfoManager()
            ->findUserInfoByUserIdAndKey($this->userId, $this->key);

        return ($identityInfo != null ? $identityInfo->getValue() : null);
    }
}
