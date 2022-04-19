<?php

namespace BpmPlatform\Engine\Impl\Cmd;

use BpmPlatform\Engine\Exception\NotValidException;
use BpmPlatform\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use BpmPlatform\Engine\Impl\Util\EnsureUtil;

class DeleteUserOperationLogEntryCmd implements CommandInterface
{
    protected $entryId;

    public function __construct(?string $entryId)
    {
        $this->entryId = $entryId;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "entryId", $this->entryId);

        $entry = $commandContext
            ->getOperationLogManager()
            ->findOperationLogById($this->entryId);

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkDeleteUserOperationLog($entry);
        }

        $commandContext->getOperationLogManager()->deleteOperationLogEntryById($this->entryId);
        return null;
    }
}
