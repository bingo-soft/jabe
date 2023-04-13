<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class DeleteUserInfoCmd implements CommandInterface, \Serializable
{
    protected $userId;
    protected $key;

    public function __construct(?string $userId, ?string $key)
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