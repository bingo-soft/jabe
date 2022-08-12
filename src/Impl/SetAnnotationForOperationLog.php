<?php

namespace Jabe\Impl;

use Jabe\BadUserRequestException;
use Jabe\Exception\NotValidException;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Util\EnsureUtil;

class SetAnnotationForOperationLog implements CommandInterface
{
    protected $operationId;
    protected $annotation;

    public function __construct(string $operationId, string $annotation)
    {
        $this->operationId = $operationId;
        $this->annotation = $annotation;
    }

    public function execute(CommandContext $commandContext)
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "operation id", $this->operationId);

        $commandContext->disableAuthorizationCheck();

        $operationLogEntries = $commandContext->getProcessEngineConfiguration()
            ->getHistoryService()
            ->createUserOperationLogQuery()
            ->operationId($this->operationId)
            ->list();

        $commandContext->enableAuthorizationCheck();

        EnsureUtil::ensureNotEmpty(BadUserRequestException::class, "operations", $operationLogEntries);

        $operationLogEntry = $this->operationLogEntries[0];

        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkUpdateUserOperationLog($operationLogEntry);
        }

        $commandContext->getOperationLogManager()
            ->updateOperationLogAnnotationByOperationId($this->operationId, $this->annotation);

        if ($this->annotation === null) {
            $commandContext->getOperationLogManager()
                ->logClearAnnotationOperation($this->operationId);
        } else {
            $commandContext->getOperationLogManager()
                ->logSetAnnotationOperation($this->operationId);
        }
        return null;
    }
}
