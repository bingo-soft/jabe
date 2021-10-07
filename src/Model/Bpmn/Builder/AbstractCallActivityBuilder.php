<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\Extension\{
    InInterface,
    OutInterface
};
use BpmPlatform\Model\Bpmn\Instance\{
    CallActivityInterface
};

abstract class AbstractCallActivityBuilder extends AbstractActivityBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        CallActivityInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function calledElement(string $calledElement): AbstractCallActivityBuilder
    {
        $this->element->setCalledElement($calledElement);
        return $this;
    }

    public function calledElementBinding(string $calledElementBinding): AbstractCallActivityBuilder
    {
        $this->element->setCalledElementBinding($calledElementBinding);
        return $this;
    }

    public function calledElementVersion(string $calledElementVersion): AbstractCallActivityBuilder
    {
        $this->element->setCalledElementVersion($calledElementVersion);
        return $this;
    }

    public function calledElementVersionTag(string $calledElementVersionTag): AbstractCallActivityBuilder
    {
        $this->element->setCalledElementVersionTag($calledElementVersionTag);
        return $this;
    }

    public function calledElementTenantId(string $calledElementTenantId): AbstractCallActivityBuilder
    {
        $this->element->setCalledElementTenantId($calledElementTenantId);
        return $this;
    }

    public function caseRef(string $caseRef): AbstractCallActivityBuilder
    {
        $this->element->setCaseRef($caseRef);
        return $this;
    }

    public function caseBinding(string $caseBinding): AbstractCallActivityBuilder
    {
        $this->element->setCaseBinding($caseBinding);
        return $this;
    }

    public function caseVersion(string $caseVersion): AbstractCallActivityBuilder
    {
        $this->element->setCaseVersion($caseVersion);
        return $this;
    }

    public function caseTenantId(string $caseTenantId): AbstractCallActivityBuilder
    {
        $this->element->setCaseTenantId($caseTenantId);
        return $this;
    }

    public function in(string $source, string $target): AbstractCallActivityBuilder
    {
        $param = $this->modelInstance->newInstance(InInterface::class);
        $param->setSource($source);
        $param->setTarget($target);
        $this->addExtensionElement($param);
        return $this;
    }

    public function out(string $source, string $target): AbstractCallActivityBuilder
    {
        $param = $this->modelInstance->newInstance(OutInterface::class);
        $param->setSource($source);
        $param->setTarget($target);
        $this->addExtensionElement($param);
        return $this;
    }

    public function variableMappingClass(string $className): AbstractCallActivityBuilder
    {
        $this->element->setVariableMappingClass($className);
        return $this;
    }

    public function variableMappingDelegateExpression(
        string $variableMappingDelegateExpression
    ): AbstractCallActivityBuilder {
        $this->element->setVariableMappingDelegateExpression($variableMappingDelegateExpression);
        return $this;
    }
}
