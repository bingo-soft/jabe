<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\IdentityInfoEntity;

class SetUserInfoCmd implements CommandInterface
{
    protected $userId;
    protected $userPassword;
    protected $type;
    protected $key;
    protected $value;
    protected $accountPassword;
    protected $accountDetails;

    public function __cosntruct(?string $userId, ?string $passwordOrKey, ?string $nameOrValue, ?string $accountUsername = null, ?string $accountPassword = null, array $accountDetails = [])
    {
        if ($accountUsername === null) {
            $this->userId = $userId;
            $this->type = IdentityInfoEntity::TYPE_USERINFO;
            $this->key = $passwordOrKey;
            $this->value = $nameOrValue;
        } else {
            $this->userId = $userId;
            $this->userPassword = $passwordOrKey;
            $this->type = IdentityInfoEntity::TYPE_USERACCOUNT;
            $this->key = $nameOrValue;
            $this->value = $accountUsername;
            $this->accountPassword = $accountPassword;
            $this->accountDetails = $accountDetails;
        }
    }

    public function __serialize(): array
    {
        return [
            'userId' => $this->userId,
            'type' => $this->type,
            'key' => $this->key,
            'value' => $this->value,
            'accountPassword' => $this->accountPassword,
            'accountDetails' => $this->accountDetails
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->userId = $data['userId'];
        $this->type = $data['type'];
        $this->key = $data['key'];
        $this->value = $data['value'];
        $this->accountPassword = $data['accountPassword'];
        $this->accountDetails = $data['accountDetails'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $commandContext
            ->getIdentityInfoManager()
            ->setUserInfo($this->userId, $this->userPassword, $this->type, $this->key, $this->value, $this->accountPassword, $this->accountDetails);
        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
