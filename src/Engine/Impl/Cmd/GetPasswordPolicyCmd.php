<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetPasswordPolicyCmd implements CommandInterface
{
    public function execute(CommandContext $commandContext)
    {
        $processEngineConfiguration = $commandContext->getProcessEngineConfiguration();
        if ($processEngineConfiguration->isEnablePasswordPolicy()) {
            return $processEngineConfiguration->getPasswordPolicy();
        } else {
            return null;
        }
    }
}
