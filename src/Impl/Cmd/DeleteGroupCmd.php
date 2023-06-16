<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class DeleteGroupCmd extends AbstractWritableIdentityServiceCmd implements CommandInterface
{
    private $groupId;

    public function __construct(?string $groupId)
    {
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
        EnsureUtil::ensureNotNull("groupId", "groupId", $this->groupId);

        $operationResult = $commandContext
            ->getWritableIdentityProvider()
            ->deleteGroup($this->groupId);

        $commandContext->getOperationLogManager()->logGroupOperation($operationResult, $this->groupId);

        return null;
    }
}
