<?php

namespace Jabe\Impl\Cmd;

use Jabe\History\UserOperationLogEntryInterface;
use Jabe\Impl\AuthorizationQueryImpl;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Impl\Persistence\Entity\{
    AuthorizationEntity,
    AuthorizationManager
};
use Jabe\Impl\Util\EnsureUtil;

class DeleteAuthorizationCmd implements CommandInterface
{
    protected $authorizationId;

    public function __construct(?string $authorizationId)
    {
        $this->authorizationId = $authorizationId;
    }

    public function execute(CommandContext $commandContext)
    {
        $authorizationManager = $commandContext->getAuthorizationManager();

        $authorization = (new AuthorizationQueryImpl())
            ->authorizationId($this->authorizationId)
            ->singleResult();

        EnsureUtil::ensureNotNull("Authorization for Id '" . $this->authorizationId . "' does not exist", "authorization", $authorization);

        $authorizationManager->delete($authorization);
        $commandContext->getOperationLogManager()->logAuthorizationOperation(UserOperationLogEntryInterface::OPERATION_TYPE_DELETE, $authorization, null);

        return null;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
