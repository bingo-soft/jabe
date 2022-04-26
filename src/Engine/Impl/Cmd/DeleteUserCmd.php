<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class DeleteUserCmd extends AbstractWritableIdentityServiceCmd implements CommandInterface, \Serializable
{
    private $userId;

    public function __construct(?string $userId)
    {
        $this->userId = $userId;
    }

    public function serialize()
    {
        return json_encode([
            'userId' => $this->userId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->userId = $json->userId;
    }

    protected function executeCmd(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("userId", $this->userId);

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
