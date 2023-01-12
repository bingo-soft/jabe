<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class DeleteGroupCmd extends AbstractWritableIdentityServiceCmd implements CommandInterface, \Serializable
{
    private $groupId;

    public function __construct(?string $groupId)
    {
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
        EnsureUtil::ensureNotNull("groupId", "groupId", $this->groupId);

        $operationResult = $commandContext
            ->getWritableIdentityProvider()
            ->deleteGroup($this->groupId);

        $commandContext->getOperationLogManager()->logGroupOperation($operationResult, $this->groupId);

        return null;
    }
}
