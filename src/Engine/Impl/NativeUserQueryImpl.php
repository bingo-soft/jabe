<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\Identity\{
    NativeUserQueryInterface,
    UserInterface
};
use Jabe\Engine\Impl\Identity\Db\DbReadOnlyIdentityServiceProvider;
use Jabe\Engine\Impl\Interceptor\{
    CommandContext,
    CommandExecutorInterface
};

class NativeUserQueryImpl extends AbstractNativeQuery implements NativeUserQueryInterface
{

    public function __construct($contextOrExecutor)
    {
        parent::__construct($contextOrExecutor);
    }

   //results ////////////////////////////////////////////////////////////////

    public function executeList(CommandContext $commandContext, array $parameterMap, int $firstResult, int $maxResults): array
    {
        $identityProvider = $this->getIdentityProvider($commandContext);
        return $identityProvider->findUserByNativeQuery($parameterMap, $firstResult, $maxResults);
    }

    public function executeCount(CommandContext $commandContext, array $parameterMap): int
    {
        $identityProvider = $this->getIdentityProvider($commandContext);
        return $identityProvider->findUserCountByNativeQuery($parameterMap);
    }

    private function getIdentityProvider(CommandContext $commandContext): DbReadOnlyIdentityServiceProvider
    {
        return $commandContext->getReadOnlyIdentityProvider();
    }
}
