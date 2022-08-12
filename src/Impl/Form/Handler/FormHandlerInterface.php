<?php

namespace Jabe\Impl\Form\Handler;

use Jabe\Delegate\VariableScopeInterface;
use Jabe\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Impl\Persistence\Entity\{
    DeploymentEntity,
    ProcessDefinitionEntity
};
use Sax\Element;
use Jabe\Variable\VariableMapInterface;

interface FormHandlerInterface
{
    public function parseConfiguration(Element $activityElement, DeploymentEntity $deployment, ProcessDefinitionEntity $processDefinition, BpmnParse $bpmnParse): void;

    public function submitFormVariables(VariableMapInterface $properties, VariableScopeInterface $variableScope): void;
}
