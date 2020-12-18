<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    CallActivityInterface,
    InInterface,
    OutInterface
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
        return $this->myself;
    }

    public function calledElementBinding(string $calledElementBinding): AbstractCallActivityBuilder
    {
        $this->element->setCalledElementBinding($calledElementBinding);
        return $this->myself;
    }

    public function calledElementVersion(string $calledElementVersion): AbstractCallActivityBuilder
    {
        $this->element->setCalledElementVersion($calledElementVersion);
        return $this->myself;
    }

    public function calledElementVersionTag(string $calledElementVersionTag): AbstractCallActivityBuilder
    {
        $this->element->setCalledElementVersionTag($calledElementVersionTag);
        return $this->myself;
    }

    public function calledElementTenantId(string $calledElementTenantId): AbstractCallActivityBuilder
    {
        $this->element->setCalledElementTenantId($calledElementTenantId);
        return $this->myself;
    }

    public function caseRef(string $caseRef): AbstractCallActivityBuilder
    {
        $this->element->setCaseRef($caseRef);
        return $this->myself;
    }

    public function caseBinding(string $caseBinding): AbstractCallActivityBuilder
    {
        $this->element->setCaseBinding($caseBinding);
        return $this->myself;
    }

    public function caseVersion(string $caseVersion): AbstractCallActivityBuilder
    {
        $this->element->setCaseVersion($caseVersion);
        return $this->myself;
    }

    public function caseTenantId(string $caseTenantId): AbstractCallActivityBuilder
    {
        $this->element->setCaseTenantId($caseTenantId);
        return $this->myself;
    }

    public function in(string $source, string $target): AbstractCallActivityBuilder
    {
        $param = $this->modelInstance->newInstance(InInterface::class);
        $param->setSource($source);
        $param->setTarget($target);
        $this->addExtensionElement($param);
        return $this->myself;
    }

    public function out(string $source, string $target): AbstractCallActivityBuilder
    {
        $param = $this->modelInstance->newInstance(OutInterface::class);
        $param->setSource($source);
        $param->setTarget($target);
        $this->addExtensionElement($param);
        return $this->myself;
    }

    public function variableMappingClass(string $className): AbstractCallActivityBuilder
    {
        $this->element->setVariableMappingClass($className);
        return $this->myself;
    }

    public function variableMappingDelegateExpression(
        string $variableMappingDelegateExpression
    ): AbstractCallActivityBuilder {
        $this->element->setVariableMappingDelegateExpression($variableMappingDelegateExpression);
        return $this->myself;
    }
}
