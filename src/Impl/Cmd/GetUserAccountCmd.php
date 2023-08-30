<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetUserAccountCmd implements CommandInterface
{
    protected $userId;
    protected $userPassword;
    protected $accountName;

    public function __construct(?string $userId, ?string $userPassword, ?string $accountName)
    {
        $this->userId = $userId;
        $this->userPassword = $userPassword;
        $this->accountName = $accountName;
    }

    public function __serialize(): array
    {
        return [
            'userId' => $this->userId,
            'userPassword' => $this->userPassword,
            'accountName' => $this->accountName
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->userId = $data['userId'];
        $this->userPassword = $data['userPassword'];
        $this->accountName = $data['accountName'];
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        return $commandContext
            ->getIdentityInfoManager()
            ->findUserAccountByUserIdAndKey($this->userId, $this->userPassword, $this->accountName);
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
