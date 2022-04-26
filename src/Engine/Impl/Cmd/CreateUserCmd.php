<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Identity\UserInterface;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class CreateUserCmd extends AbstractWritableIdentityServiceCmd implements CommandInterface, \Serializable
{
    protected $userId;

    public function __construct(string $userId)
    {
        EnsureUtil::ensureNotNull("userId", "userId", $userId);
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

    protected function executeCmd(CommandContext $commandContext): UserInterface
    {
        return $commandContext
            ->getWritableIdentityProvider()
            ->createNewUser($this->userId);
    }
}
