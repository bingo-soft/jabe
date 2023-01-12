<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class CreateGroupCmd extends AbstractWritableIdentityServiceCmd implements CommandInterface, \Serializable
{
    protected $groupId;

    public function __construct(?string $groupId)
    {
        EnsureUtil::ensureNotNull("groupId", "groupId", $groupId);
        $this->groupId = $groupId;
    }

    public function serialize()
    {
        return json_encode([
            'groupId' => $this->groupId
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->groupId = $json->groupId;
    }

    protected function executeCmd(CommandContext $commandContext)
    {
        return $commandContext
            ->getWritableIdentityProvider()
            ->createNewGroup($this->groupId);
    }
}
