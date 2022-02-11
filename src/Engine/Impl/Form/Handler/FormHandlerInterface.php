<?php

namespace BpmPlatform\Engine\Impl\Form\Handler;

use BpmPlatform\Engine\Delegate\VariableScopeInterface;
use BpmPlatform\Engine\Impl\Bpmn\Parser\BpmnParse;
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    DeploymentEntity,
    ProcessDefinitionEntity
};
use BpmPlatform\Engine\Impl\Util\Xml\Element;
use BpmPlatform\Engine\Variable\VariableMapInterface;

interface FormHandlerInterface
{
    public function parseConfiguration(Element $activityElement, DeploymentEntity $deployment, ProcessDefinitionEntity $processDefinition, BpmnParse $bpmnParse): void;

    public function submitFormVariables(VariableMapInterface $properties, VariableScopeInterface $variableScope): void;
}
