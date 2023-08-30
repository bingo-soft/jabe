<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class CreateGroupCmd extends AbstractWritableIdentityServiceCmd implements CommandInterface
{
    protected $groupId;

    public function __construct(?string $groupId)
    {
        EnsureUtil::ensureNotNull("groupId", "groupId", $groupId);
        $this->groupId = $groupId;
    }

    public function __serialize(): array
    {
        return [
            'groupId' => $this->groupId
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->groupId = $data['groupId'];
    }

    protected function executeCmd(CommandContext $commandContext)
    {
        return $commandContext
            ->getWritableIdentityProvider()
            ->createNewGroup($this->groupId);
    }
}
