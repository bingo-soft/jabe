<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Application\ProcessApplicationReferenceInterface;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class RegisterProcessApplicationCmd implements CommandInterface
{
    protected $reference;
    protected $deploymentsToRegister;

    public function __construct($deployment, ProcessApplicationReferenceInterface $appReference)
    {
        $this->deploymentsToRegister = is_array($deployment) ? $deployment : [$deployment];
        $this->reference = $appReference;
    }

    public function execute(CommandContext $commandContext)
    {
        $commandContext->getAuthorizationManager()->checkAdminOrPermission("checkRegisterProcessApplication");

        $processEngineConfiguration = Context::getProcessEngineConfiguration();
        $processApplicationManager = $processEngineConfiguration->getProcessApplicationManager();

        return $processApplicationManager->registerProcessApplicationForDeployments($this->deploymentsToRegister, $this->reference);
    }
}
