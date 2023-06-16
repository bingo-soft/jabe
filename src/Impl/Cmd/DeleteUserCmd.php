<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class DeleteUserCmd extends AbstractWritableIdentityServiceCmd implements CommandInterface
{
    private $userId;

    public function __construct(?string $userId)
    {
        $this->userId = $userId;
    }

    public function __serialize(): array
    {
        return [
            'userId' => $this->userId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->userId = $data['userId'];
    }

    protected function executeCmd(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("userId", "userId", $this->userId);

        // delete user picture
        (new DeleteUserPictureCmd($this->userId))->execute($commandContext);

        $commandContext->getIdentityInfoManager()
            ->deleteUserInfoByUserId($this->userId);

        $operationResult = $commandContext
            ->getWritableIdentityProvider()
            ->deleteUser($this->userId);

        $commandContext->getOperationLogManager()->logUserOperation($operationResult, $this->userId);

        return null;
    }
}
