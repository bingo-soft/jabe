<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\History\UserOperationLogEntryInterface;
use Jabe\Engine\Impl\AuthorizationQueryImpl;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\{
    AuthorizationEntity,
    AuthorizationManager
};
use Jabe\Engine\Impl\Util\EnsureUtil;

class DeleteAuthorizationCmd implements CommandInterface
{
    protected $authorizationId;

    public function __construct(string $authorizationId)
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
}
