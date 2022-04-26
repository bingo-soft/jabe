<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Identity\GroupInterface;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class SaveGroupCmd extends AbstractWritableIdentityServiceCmd implements CommandInterface, \Serializable
{
    protected $group;

    public function __construct(GroupInterface $group)
    {
        $this->group = $group;
    }

    public function serialize()
    {
        return json_encode([
            'group' => serialize($this->group)
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->group = unserialize($json->group);
    }

    protected function executeCmd(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull("group", "group", $this->group);
        EnsureUtil::ensureWhitelistedResourceId($commandContext, "Group", $this->group->getId());

        $operationResult = $commandContext
            ->getWritableIdentityProvider()
            ->saveGroup($this->group);

        $commandContext->getOperationLogManager()->logGroupOperation($operationResult, $this->group->getId());

        return null;
    }
}
