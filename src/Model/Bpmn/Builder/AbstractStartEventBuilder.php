<?php

namespace BpmPlatform\Model\Bpmn\Builder;

use BpmPlatform\Model\Bpmn\Exception\BpmnModelException;
use BpmPlatform\Model\Bpmn\BpmnModelInstanceInterface;
use BpmPlatform\Model\Bpmn\Instance\{
    ErrorEventDefinitionInterface,
    EscalationEventDefinitionInterface,
    FormDataInterface,
    FormFieldInterface,
    StartEventInterface
};

abstract class AbstractStartEventBuilder extends AbstractCatchEventBuilder
{
    protected function __construct(
        BpmnModelInstanceInterface $modelInstance,
        StartEventInterface $element,
        string $selfType
    ) {
        parent::__construct($modelInstance, $element, $selfType);
    }

    public function formHandlerClass(string $className): AbstractStartEventBuilder
    {
        $this->element->setFormHandlerClass($className);
        return $this->myself;
    }

    public function formKey(string $formKey): AbstractStartEventBuilder
    {
        $this->element->setFormKey($formKey);
        return $this->myself;
    }

    public function initiator(string $initiator): AbstractStartEventBuilder
    {
        $this->element->setInitiator($initiator);
        return $this->myself;
    }

    public function formField(): StartEventFormFieldBuilder
    {
        $formData = $this->getCreateSingleExtensionElement(FormDataInterface::class);
        $formField = $this->createChild($formData, FormFieldInterface::class);
        return new StartEventFormFieldBuilder($this->modelInstance, $element, $formField);
    }

    public function error(?string $errorCode, ?string $errorMessage): AbstractStartEventBuilder
    {
        if ($errorCode == null) {
            $errorEventDefinition = $this->createInstance(ErrorEventDefinitionInterface::class);
        } else {
            $errorEventDefinition = $this->createErrorEventDefinition($errorCode, $errorMessage);
        }
        $this->element->addEventDefinition($errorEventDefinition);

        return $this->myself;
    }

    public function errorEventDefinition(?string $id): ErrorEventDefinitionBuilder
    {
        $errorEventDefinition = $this->createEmptyErrorEventDefinition();
        if ($id != null) {
            $errorEventDefinition->setId($id);
        }
        $this->element->addEventDefinition($errorEventDefinition);
        return new ErrorEventDefinitionBuilder($this->modelInstance, $errorEventDefinition);
    }

    public function escalation(?string $escalationCode): AbstractStartEventBuilder
    {
        if ($escalationCode == null) {
            $escalationEventDefinition = $this->createInstance(EscalationEventDefinitionInterface::class);
        } else {
            $escalationEventDefinition = $this->createEscalationEventDefinition($escalationCode);
        }
        $this->element->addEventDefinition($errorEventDefinition);
        return $this->myself;
    }

    public function compensation(): AbstractStartEventBuilder
    {
        $compensateEventDefinition = $this->createCompensateEventDefinition();
        $this->element->addEventDefinition($compensateEventDefinition);
        return $this->myself;
    }

    public function interrupting(bool $interrupting): AbstractStartEventBuilder
    {
        $this->element->setInterrupting($interrupting);
        return $this->myself;
    }
}
