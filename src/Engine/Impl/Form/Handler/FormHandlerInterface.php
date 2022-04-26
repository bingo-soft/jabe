<?php

namespace Jabe\Engine\Impl\Form\Handler;

use Jabe\Engine\Delegate\VariableScopeInterface;
use Jabe\Engine\Impl\Bpmn\Parser\BpmnParse;
use Jabe\Engine\Impl\Persistence\Entity\{
    DeploymentEntity,
    ProcessDefinitionEntity
};
use Jabe\Engine\Impl\Util\Xml\Element;
use Jabe\Engine\Variable\VariableMapInterface;

interface FormHandlerInterface
{
    public function parseConfiguration(Element $activityElement, DeploymentEntity $deployment, ProcessDefinitionEntity $processDefinition, BpmnParse $bpmnParse): void;

    public function submitFormVariables(VariableMapInterface $properties, VariableScopeInterface $variableScope): void;
}
