<?php

namespace Jabe\Model\Bpmn\Builder;

use Jabe\Model\Bpmn\BpmnModelInstanceInterface;
use Jabe\Model\Bpmn\Instance\Extension\InInterface;
use Jabe\Model\Bpmn\Instance\SignalEventDefinitionInterface;

abstract class AbstractSignalEventDefinitionBuilder extends AbstractRootElementBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        SignalEventDefinitionInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function inSourceTarget(string $source, string $target): AbstractSignalEventDefinitionBuilder
    {
        $param = $this->modelInstance->newInstance(InInterface::class);
        $param->setSource($source);
        $param->setTarget($target);

        $this->addExtensionElement($param);
        return $this;
    }

    public function inSourceExpressionTarget(string $source, string $target): AbstractSignalEventDefinitionBuilder
    {
        $param = $this->modelInstance->newInstance(InInterface::class);
        $param->setSourceExpression($source);
        $param->setTarget($target);

        $this->addExtensionElement($param);
        return $this;
    }

    public function inBusinessKey(string $businessKey): AbstractSignalEventDefinitionBuilder
    {
        $param = $this->modelInstance->newInstance(InInterface::class);
        $param->setBusinessKey($businessKey);
        $this->addExtensionElement($param);
        return $this;
    }

    public function inAllVariables(string $variables, bool $local = false): AbstractSignalEventDefinitionBuilder
    {
        $param = $this->modelInstance->newInstance(InInterface::class);
        $param->setVariables($variables);
        $param->setLocal($local);
        $this->addExtensionElement($param);
        return $this;
    }
}
