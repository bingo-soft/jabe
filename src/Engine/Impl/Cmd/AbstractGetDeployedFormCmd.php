<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\BadUserRequestException;
use Jabe\Engine\Exception\{
    DeploymentResourceNotFoundException,
    NotFoundException
};
use Jabe\Engine\Form\{
    FormRefInterface,
    FormDataInterface
};
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};

abstract class AbstractGetDeployedFormCmd implements CommandInterface
{
    protected const EMBEDDED_KEY = "embedded:";
    protected const FORMS_KEY = "forms:";
    protected const EMBEDDED_KEY_LENGTH = 9;
    protected const FORMS_KEY_LENGTH = 6;

    protected const DEPLOYMENT_KEY = "deployment:";
    protected const DEPLOYMENT_KEY_LENGTH = 11;

    protected $commandContext;

    public function execute(CommandContext $commandContext)
    {
        $this->commandContext = $commandContext;
        $this->checkAuthorization();

        $formData = $this->getFormData();
        $formKey = $formData->getFormKey();
        $formRef = $formData->getFormRef();

        if ($formKey !== null) {
            return $this->getResourceForFormKey($formData, $formKey);
        } elseif ($formRef !== null && $formRef->getKey() !== null) {
            return $this->getResourceForFormRef($formRef, $formData->getDeploymentId());
        } else {
            throw new BadUserRequestException("One of the attributes 'formKey' and 'camunda:formRef' must be supplied but none were set.");
        }
    }

    protected function getResourceForFormKey(FormDataInterface $formData, string $formKey)
    {
        $resourceName = $formKey;

        if (strpos($resourceName, self::EMBEDDED_KEY) === 0) {
            $resourceName = substr($resourceName, self::EMBEDDED_KEY_LENGTH, strlen($resourceName));
        } elseif (strpos($resourceName, self::FORMS_KEY) === 0) {
            $resourceName = substr($resourceName, self::FORMS_KEY_LENGTH, strlen($resourceName));
        }

        if (strpos($resourceName, self::DEPLOYMENT_KEY) !== 0) {
            throw new BadUserRequestException("The form key '" . $formKey . "' does not reference a deployed form.");
        }

        $resourceName = substr($resourceName, self::DEPLOYMENT_KEY_LENGTH, strlen($resourceName));

        return $this->getDeploymentResource($formData->getDeploymentId(), $resourceName);
    }

    protected function getResourceForFormRef(FormRefInterface $formRef, string $deploymentId)
    {
        $ctx = $this->commandContext;
        $definition = $ctx->runWithoutAuthorization(function () use ($ctx, $formRef, $deploymentId) {
            $cmd = new GetCamundaFormDefinitionCmd($formRef, $deploymentId);
            return $cmd->execute($ctx);
        });

        if ($definition === null) {
            throw new NotFoundException("No Form Definition was found for Form Ref: " . $formRef);
        }

        return $this->getDeploymentResource($definition->getDeploymentId(), $definition->getResourceName());
    }

    protected function getDeploymentResource(string $deploymentId, string $resourceName)
    {
        try {
            $ctx = $this->commandContext;
            return $ctx->runWithoutAuthorization(function () use ($ctx, $deploymentId, $resourceName) {
                $cmd = new GetDeploymentResourceCmd($deploymentId, $resourceName);
                $cmd->execute($ctx);
            });
        } catch (DeploymentResourceNotFoundException $e) {
            throw new NotFoundException("The form with the resource name '" . $resourceName . "' cannot be found in deployment with id " . $deploymentId, $e);
        }
    }

    abstract protected function getFormData(): FormDataInterface;

    abstract protected function checkAuthorization(): void;
}
