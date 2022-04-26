<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Exception\NotValidException;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Util\EnsureUtil;

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
