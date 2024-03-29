<?php

namespace Jabe\Impl\Cmd;

use Jabe\BadUserRequestException;
use Jabe\Form\FormRefInterface;
use Jabe\Impl\Form\Handler\DefaultFormHandler;
use Jabe\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

class GetFormDefinitionCmd implements CommandInterface
{
    protected $formRef;
    protected $deploymentId;

    public function __construct(FormRefInterface $formRef, ?string $deploymentId)
    {
        $this->formRef = $formRef;
        $this->deploymentId = $deploymentId;
    }

    public function execute(CommandContext $commandContext, ...$args)
    {
        $binding = $this->formRef->getBinding();
        $key = $this->formRef->getKey();
        $definition = null;
        $manager = $commandContext->getFormDefinitionManager();
        if ($binding == DefaultFormHandler::FORM_REF_BINDING_DEPLOYMENT) {
            $definition = $manager->findDefinitionByDeploymentAndKey($this->deploymentId, $key);
        } elseif ($binding == DefaultFormHandler::FORM_REF_BINDING_LATEST) {
            $definition = $manager->findLatestDefinitionByKey($key);
        } elseif ($binding == DefaultFormHandler::FORM_REF_BINDING_VERSION) {
            $definition = $manager->findDefinitionByKeyVersionAndTenantId($key, $this->formRef->getVersion(), null);
        } else {
            throw new BadUserRequestException("Unsupported binding type for formRef. Expected to be one of "
                . DefaultFormHandler::ALLOWED_FORM_REF_BINDINGS . " but was:" . $binding);
        }

        return $definition;
    }

    public function isRetryable(): bool
    {
        return false;
    }
}
