<?php

namespace Jabe\Engine\Impl\Cmd;

use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\ProcessDefinitionEntity;
use Jabe\Engine\Variable\Impl\VariableMapImpl;

class GetStartFormVariablesCmd extends AbstractGetFormVariablesCmd
{
    public function __construct(string $resourceId, array $formVariableNames, bool $deserializeObjectValues)
    {
        parent::__construct($resourceId, $formVariableNames, $deserializeObjectValues);
    }

    public function execute(CommandContext $commandContext)
    {
        $resourceId = $this->resourceId;
        $startFormData = $commandContext->runWithoutAuthorization(function () use ($commandContext, $resourceId) {
            $cmd = new GetStartFormCmd($resourceId);
            return $cmd->execute($commandContext);
        });

        $definition = $startFormData->getProcessDefinition();
        $this->checkGetStartFormVariables($definition, $commandContext);

        $result = new VariableMapImpl();

        foreach ($startFormData->getFormFields() as $formField) {
            if (empty($this->formVariableNames) || in_array($formField->getId(), $this->formVariableNames)) {
                $result->put($formField->getId(), $this->createVariable($formField, null));
            }
        }

        return $result;
    }

    protected function checkGetStartFormVariables(ProcessDefinitionEntity $definition, CommandContext $commandContext): void
    {
        foreach ($commandContext->getProcessEngineConfiguration()->getCommandCheckers() as $checker) {
            $checker->checkReadProcessDefinition($definition);
        }
    }
}
