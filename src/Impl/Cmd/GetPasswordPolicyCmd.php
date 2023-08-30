<?php

namespace Jabe\Impl\Cmd;

use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetPasswordPolicyCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext, ...$args)
    {
        $processEngineConfiguration = $commandContext->getProcessEngineConfiguration();
        if ($processEngineConfiguration->isEnablePasswordPolicy()) {
            return $processEngineConfiguration->getPasswordPolicy();
        } else {
            return null;
        }
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
