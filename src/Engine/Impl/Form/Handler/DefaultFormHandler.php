<?php

namespace BpmPlatform\Engine\Impl\Form\Handler;

use BpmPlatform\Engine\Delegate\{
    ExpressionInterface,
    VariableScopeInterface
};
use BpmPlatform\Engine\Form\{
    FormFieldInterface,
    FormPropertyInterface
};
use BpmPlatform\Engine\Impl\Bpmn\Parser\BpmnParse;
use BpmPlatform\Engine\Impl\Cfg\ProcessEngineConfigurationImpl;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\El\ExpressionManager;
use BpmPlatform\Engine\Impl\Form\{
    FormDataImpl,
    FormDefinition
};
use BpmPlatform\Engine\Impl\Form\Type\{
    AbstractFormFieldType,
    FormTypes
};
use BpmPlatform\Engine\Impl\Form\Validator\FormFieldValidatorInterface;
use BpmPlatform\Engine\Impl\History\HistoryLevel;
use BpmPlatform\Engine\Impl\History\Event\{
    HistoryEvent,
    HistoryEventCreator,
    HistoryEventProcessor,
    HistoryEventTypes
};
use BpmPlatform\Engine\Impl\History\Producer\HistoryEventProducerInterface;
use BpmPlatform\Engine\Impl\Persistence\Entity\{
    DeploymentEntity,
    ExecutionEntity,
    ProcessDefinitionEntity,
    TaskEntity
};
use BpmPlatform\Engine\Impl\Util\Xml\Element;
use BpmPlatform\Engine\Variable\VariableMapInterface;
use BpmPlatform\Engine\Variable\Impl\VariableMapImpl;
use BpmPlatform\Engine\Variable\Value\{
    SerializableValueInterface,
    TypedValueInterface
};

class DefaultFormHandler implements FormHandlerInterface
{
    public const FORM_FIELD_ELEMENT = "formField";
    public const FORM_PROPERTY_ELEMENT = "formProperty";
    private const BUSINESS_KEY_ATTRIBUTE = "businessKey";

    public const FORM_REF_BINDING_DEPLOYMENT = "deployment";
    public const FORM_REF_BINDING_LATEST = "latest";
    public const FORM_REF_BINDING_VERSION = "version";
    public const ALLOWED_FORM_REF_BINDINGS = [self::FORM_REF_BINDING_DEPLOYMENT, self::FORM_REF_BINDING_LATEST, self::FORM_REF_BINDING_VERSION];

    protected $deploymentId;
    protected $businessKeyFieldId;

    protected $formPropertyHandlers = [];

    protected $formFieldHandlers = [];

    public function parseConfiguration(
        Element $activityElement,
        DeploymentEntity $deployment,
        ProcessDefinitionEntity $processDefinition,
        BpmnParse $bpmnParse
    ): void {
        $this->deploymentId = $deployment->getId();

        $expressionManager = Context::getProcessEngineConfiguration()
            ->getExpressionManager();

        $extensionElement = $activityElement->element("extensionElements");
        if ($extensionElement != null) {
            // provide support for deprecated form properties
            $this->parseFormProperties($bpmnParse, $expressionManager, $extensionElement);

            // provide support for new form field metadata
            $this->parseFormData($bpmnParse, $expressionManager, $extensionElement);
        }
    }

    protected function parseFormData(BpmnParse $bpmnParse, ExpressionManager $expressionManager, Element $extensionElement): void
    {
        $formData = $extensionElement->elementNS(BpmnParse::BPMN_EXTENSIONS_NS_PREFIX, "formData");
        if ($formData != null) {
            $this->businessKeyFieldId = $formData->attribute(self::BUSINESS_KEY_ATTRIBUTE);
            $this->parseFormFields($formData, $bpmnParse, $expressionManager);
        }
    }

    protected function parseFormFields(Element $formData, BpmnParse $bpmnParse, ExpressionManager $expressionManager): void
    {
        // parse fields:
        $formFields = $formData->elementsNS(BpmnParse::BPMN_EXTENSIONS_NS_PREFIX, self::FORM_FIELD_ELEMENT);
        foreach ($formFields as $formField) {
            $this->parseFormField($formField, $bpmnParse, $expressionManager);
        }
    }

    protected function parseFormField(Element $formField, BpmnParse $bpmnParse, ExpressionManager $expressionManager): void
    {
        $formFieldHandler = new FormFieldHandler();

        // parse Id
        $id = $formField->attribute("id");
        if (empty($id)) {
            $bpmnParse->addError("attribute id must be set for FormFieldGroup and must have a non-empty value", $formField);
        } else {
            $formFieldHandler->setId($id);
        }

        if ($id == $this->businessKeyFieldId) {
            $formFieldHandler->setBusinessKey(true);
        }

        // parse name
        $name = $formField->attribute("label");
        if ($name != null) {
            $nameExpression = $expressionManager->createExpression($name);
            $formFieldHandler->setLabel($nameExpression);
        }

        // parse properties
        $this->parseProperties($formField, $formFieldHandler, $bpmnParse, $expressionManager);

        // parse validation
        $this->parseValidation($formField, $formFieldHandler, $bpmnParse, $expressionManager);

        // parse type
        $formTypes = $this->getFormTypes();
        $formType = $formTypes->parseFormPropertyType($formField, $bpmnParse);
        $formFieldHandler->setType($formType);

        // parse default value
        $defaultValue = $formField->attribute("defaultValue");
        if ($defaultValue != null) {
            $defaultValueExpression = $expressionManager->createExpression($defaultValue);
            $formFieldHandler->setDefaultValueExpression($defaultValueExpression);
        }

        $this->formFieldHandlers[] = $formFieldHandler;
    }

    protected function parseProperties(
        Element $formField,
        FormFieldHandler $formFieldHandler,
        BpmnParse $bpmnParse,
        ExpressionManager $expressionManager
    ): void {
        $propertiesElement = $formField->elementNS(BpmnParse::BPMN_EXTENSIONS_NS_PREFIX, "properties");

        if ($propertiesElement != null) {
            $propertyElements = $propertiesElement->elementsNS(BpmnParse::BPMN_EXTENSIONS_NS_PREFIX, "property");

            // use linked hash map to preserve item ordering as provided in XML
            $propertyMap = [];
            foreach ($propertyElements as $property) {
                $id = $property->attribute("id");
                $value = $property->attribute("value");
                $propertyMap[$id] = $value;
            }

            $formFieldHandler->setProperties($propertyMap);
        }
    }

    protected function parseValidation(Element $formField, FormFieldHandler $formFieldHandler, BpmnParse $bpmnParse, ExpressionManager $expressionManager): void
    {
        $validationElement = $formField->elementNS(BpmnParse::BPMN_EXTENSIONS_NS_PREFIX, "validation");

        if ($validationElement != null) {
            $constraintElements = $validationElement->elementsNS(BpmnParse::BPMN_EXTENSIONS_NS_PREFIX, "constraint");

            foreach ($constraintElements as $property) {
                $validator = Context::getProcessEngineConfiguration()
                    ->getFormValidators()
                    ->createValidator($property, $bpmnParse, $expressionManager);

                $validatorName = $property->attribute("name");
                $validatorConfig = $property->attribute("config");

                if ($validator != null) {
                    $handler = new FormFieldValidationConstraintHandler();
                    $handler->setName($validatorName);
                    $handler->setConfig($validatorConfig);
                    $handler->setValidator($validator);
                    $formFieldHandler->addValidationHandler($handler);
                }
            }
        }
    }

    protected function getFormTypes(): FormTypes
    {
        $formTypes = Context::getProcessEngineConfiguration()
            ->getFormTypes();
        return $formTypes;
    }

    protected function parseFormProperties(BpmnParse $bpmnParse, ExpressionManager $expressionManager, Element $extensionElement): void
    {
        $formTypes = $this->getFormTypes();

        $formPropertyElements = $extensionElement->elementsNS(BpmnParse::BPMN_EXTENSIONS_NS_PREFIX, self::FORM_PROPERTY_ELEMENT);
        foreach ($formPropertyElements as $formPropertyElement) {
            $formPropertyHandler = new FormPropertyHandler();

            $id = $formPropertyElement->attribute("id");
            if ($id == null) {
                $bpmnParse->addError("attribute 'id' is required", $formPropertyElement);
            }
            $formPropertyHandler->setId($id);

            $name = $formPropertyElement->attribute("name");
            $formPropertyHandler->setName($name);

            $type = $formTypes->parseFormPropertyType($formPropertyElement, $bpmnParse);
            $formPropertyHandler->setType($type);

            $requiredText = $formPropertyElement->attribute("required", "false");
            $required = $bpmnParse->parseBooleanAttribute($requiredText);
            if ($required != null) {
                $formPropertyHandler->setRequired($required);
            } else {
                $bpmnParse->addError("attribute 'required' must be one of {on|yes|true|enabled|active|off|no|false|disabled|inactive}", $formPropertyElement);
            }

            $readableText = $formPropertyElement->attribute("readable", "true");
            $readable = $bpmnParse->parseBooleanAttribute($readableText);
            if ($readable != null) {
                $formPropertyHandler->setReadable($readable);
            } else {
                $bpmnParse->addError("attribute 'readable' must be one of {on|yes|true|enabled|active|off|no|false|disabled|inactive}", $formPropertyElement);
            }

            $writableText = $formPropertyElement->attribute("writable", "true");
            $writable = $bpmnParse->parseBooleanAttribute($writableText);
            if ($writable != null) {
                $formPropertyHandler->setWritable($writable);
            } else {
                $bpmnParse->addError("attribute 'writable' must be one of {on|yes|true|enabled|active|off|no|false|disabled|inactive}", $formPropertyElement);
            }

            $variableName = $formPropertyElement->attribute("variable");
            $formPropertyHandler->setVariableName($variableName);

            $expressionText = $formPropertyElement->attribute("expression");
            if ($expressionText != null) {
                $expression = $expressionManager->createExpression($expressionText);
                $formPropertyHandler->setVariableExpression($expression);
            }

            $defaultExpressionText = $formPropertyElement->attribute("default");
            if ($defaultExpressionText != null) {
                $defaultExpression = $expressionManager->createExpression($defaultExpressionText);
                $formPropertyHandler->setDefaultExpression($defaultExpression);
            }

            $this->formPropertyHandlers[] = $formPropertyHandler;
        }
    }

    protected function initializeFormProperties(FormDataImpl $formData, ExecutionEntity $execution): void
    {
        $formProperties = [];
        foreach ($formPropertyHandlers as $formPropertyHandler) {
            if ($formPropertyHandler->isReadable()) {
                $formProperty = $formPropertyHandler->createFormProperty($execution);
                $formProperties[] = $formProperty;
            }
        }
        $formData->setFormProperties($formProperties);
    }

    protected function initializeFormFields(FormDataImpl $taskFormData, ExecutionEntity $execution): void
    {
        // add form fields
        $formFields = $taskFormData->getFormFields();
        foreach ($formFieldHandlers as $formFieldHandler) {
            $taskFormData->addFormField($formFieldHandler->createFormField($execution));
        }
    }

    public function submitFormVariables(VariableMapInterface $properties, VariableScopeInterface $variableScope): void
    {
        $userOperationLogEnabled = Context::getCommandContext()->isUserOperationLogEnabled();
        Context::getCommandContext()->enableUserOperationLog();

        $propertiesCopy = new VariableMapImpl($properties);

        // support legacy form properties
        foreach ($formPropertyHandlers as $formPropertyHandler) {
            // submitFormProperty will remove all the keys which it takes care of
            $formPropertyHandler->submitFormProperty($variableScope, $propertiesCopy);
        }

        // support form data:
        foreach ($formFieldHandlers as $formFieldHandler) {
            if (!$formFieldHandler->isBusinessKey()) {
                $formFieldHandler->handleSubmit($variableScope, $propertiesCopy, $properties);
            }
        }

        // any variables passed in which are not handled by form-fields or form
        // properties are added to the process as variables
        foreach (array_keys($propertiesCopy) as $propertyId) {
            $variableScope->setVariable($propertyId, $propertiesCopy->getValueTyped($propertyId));
        }

        $this->fireFormPropertyHistoryEvents($properties, $variableScope);

        Context::getCommandContext()->setLogUserOperationEnabled($userOperationLogEnabled);
    }

    protected function fireFormPropertyHistoryEvents(VariableMapInterface $properties, VariableScopeInterface $variableScope): void
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();
        $historyLevel = $processEngineConfiguration->getHistoryLevel();

        if ($historyLevel->isHistoryEventProduced(HistoryEventTypes::formPropertyUpdate(), $variableScope)) {
            // fire history events
            $executionEntity = null;
            $taskId = null;
            if ($variableScope instanceof ExecutionEntity) {
                $executionEntity = $variableScope;
                $taskId = null;
            } elseif ($variableScope instanceof TaskEntity) {
                $task = $variableScope;
                $executionEntity = $task->getExecution();
                $taskId = $task->getId();
            } else {
                $executionEntity = null;
                $taskId = null;
            }

            if ($executionEntity != null) {
                foreach (array_keys($properties) as $variableName) {
                    $value = $properties->getValueTyped($variableName);

                    // NOTE: SerializableValues are never stored as form properties
                    if (
                        !($value instanceof SerializableValueInterface)
                        && $value->getValue() != null
                        && is_string($value->getValue())
                    ) {
                        $stringValue = strval($value->getValue());

                        HistoryEventProcessor::processHistoryEvents(new class ($executionEntity, $variableName, $stringValue, $taskId) extends HistoryEventCreator {
                            private $executionEntity;
                            private $variableName;
                            private $stringValue;
                            private $taskId;

                            public function __construct($executionEntity, $variableName, $stringValue, $taskId = null)
                            {
                                $this->executionEntity = $executionEntity;
                                $this->variableName = $variableName;
                                $this->stringValue = $stringValue;
                                $this->taskId = $taskId;
                            }

                            public function createHistoryEvent(HistoryEventProducerInterface $producer): HistoryEvent
                            {
                                return $producer->createFormPropertyUpdateEvt($this->executionEntity, $this->variableName, $this->stringValue, $this->taskId);
                            }
                        });
                    }
                }
            }
        }
    }

    // getters and setters //////////////////////////////////////////////////////

    public function getDeploymentId(): string
    {
        return $this->deploymentId;
    }

    public function setDeploymentId(string $deploymentId): void
    {
        $this->deploymentId = $deploymentId;
    }

    public function getFormPropertyHandlers(): array
    {
        return $this->formPropertyHandlers;
    }

    public function setFormPropertyHandlers(array $formPropertyHandlers): void
    {
        $this->formPropertyHandlers = $formPropertyHandlers;
    }

    public function getBusinessKeyFieldId(): string
    {
        return $this->businessKeyFieldId;
    }

    public function setBusinessKeyFieldId(string $businessKeyFieldId): void
    {
        $this->businessKeyFieldId = $businessKeyFieldId;
    }
}
