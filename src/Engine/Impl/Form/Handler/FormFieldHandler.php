<?php

namespace Jabe\Engine\Impl\Form\Handler;

use Jabe\Engine\Delegate\{
    ExpressionInterface,
    VariableScopeInterface
};
use Jabe\Engine\Form\{
    FormFieldInterface,
    FormFieldValidationConstraintInterface,
    FormTypeInterface
};
use Jabe\Engine\Impl\El\StartProcessVariableScope;
use Jabe\Engine\Impl\Form\{
    FormDataImpl,
    FormFieldImpl
};
use Jabe\Engine\Impl\Form\Type\AbstractFormFieldType;
use Jabe\Engine\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Engine\Variable\{
    VariableMapInterface,
    Variables
};

class FormFieldHandler
{
    protected $id;
    protected $label;
    protected $type;
    protected $defaultValueExpression;
    protected $properties = [];
    protected $validationHandlers = [];
    protected $businessKey;

    public function createFormField(ExecutionEntity $executionEntity): FormFieldInterface
    {
        $formField = new FormFieldImpl();

        // set id
        $formField->setId($this->id);

        // set label (evaluate expression)
        $variableScope = $executionEntity !== null ? $executionEntity : StartProcessVariableScope::getSharedInstance();
        if ($this->label !== null) {
            $labelValueObject = $this->label->getValue($variableScope);
            if ($labelValueObject !== null) {
                $formField->setLabel(strval($labelValueObject));
            }
        }

        $formField->setBusinessKey($this->businessKey);

        // set type
        $formField->setType($this->type);

        // set default value (evaluate expression)
        $defaultValue = null;
        if ($this->defaultValueExpression !== null) {
            $defaultValue = $this->defaultValueExpression->getValue($variableScope);

            if ($defaultValue !== null) {
                $formField->setDefaultValue($this->type->convertFormValueToModelValue($defaultValue));
            } else {
                $formField->setDefaultValue(null);
            }
        }

        // value
        $value = $variableScope->getVariableTyped($this->id);
        if ($value !== null) {
            $formValue = null;
            try {
                $formValue = $this->type->convertToFormValue($value);
            } catch (\Exception $exception) {
                throw $exception;
            }
            $formField->setValue($formValue);
        } else {
            // first, need to convert to model value since the default value may be a String Constant specified in the model xml.
            $typedDefaultValue = $this->type->convertToModelValue(Variables::untypedValue($defaultValue));
            // now convert to form value
            $formField->setValue($this->type->convertToFormValue($typedDefaultValue));
        }

        // properties
        $formField->setProperties($this->properties);

        // validation
        foreach ($this->validationHandlers as $validationHandler) {
            // do not add custom validators
            if ("validator" != $validationHandler->name) {
                $formField->addValidationConstraint($validationHandler->createValidationConstraint($executionEntity));
            }
        }

        return $formField;
    }

    // submit /////////////////////////////////////////////

    public function handleSubmit(VariableScopeInterface $variableScope, VariableMapInterface $values, VariableMapInterface $allValues): void
    {
        $submittedValue = $values->getValueTyped($this->id);
        $values->remove($this->id);

        // perform validation
        foreach ($this->validationHandlers as $validationHandler) {
            $value = null;
            if ($submittedValue !== null) {
                $value = $submittedValue->getValue();
            }
            $validationHandler->validate($value, $allValues, $this, $variableScope);
        }

        // update variable(s)
        $modelValue = null;
        if ($submittedValue !== null) {
            if ($this->type !== null) {
                $modelValue = $this->type->convertToModelValue($submittedValue);
            } else {
                $modelValue = $submittedValue;
            }
        } elseif ($this->defaultValueExpression !== null) {
            $expressionValue = Variables::untypedValue($this->defaultValueExpression->getValue($variableScope));
            if ($this->type !== null) {
                // first, need to convert to model value since the default value may be a String Constant specified in the model xml.
                $modelValue = $this->type->convertToModelValue(Variables::untypedValue($expressionValue));
            } elseif ($expressionValue !== null) {
                $modelValue = Variables::stringValue(strval($expressionValue->getValue()));
            }
        }

        if ($modelValue !== null) {
            if ($this->id !== null) {
                $variableScope->setVariable($this->id, $modelValue);
            }
        }
    }

    // getters / setters //////////////////////////////////

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getLabel(): ExpressionInterface
    {
        return $this->label;
    }

    public function setLabel(ExpressionInterface $name): void
    {
        $this->label = $name;
    }

    public function setType(AbstractFormFieldType $formType): void
    {
        $this->type = $formType;
    }

    public function setProperties(array $properties): void
    {
        $this->properties = $properties;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getType(): FormTypeInterface
    {
        return $this->type;
    }

    public function getDefaultValueExpression(): ExpressionInterface
    {
        return $this->defaultValueExpression;
    }

    public function setDefaultValueExpression(ExpressionInterface $defaultValue): void
    {
        $this->defaultValueExpression = $defaultValue;
    }

    public function getValidationHandlers(): array
    {
        return $this->validationHandlers;
    }

    public function setValidationHandlers(array $validationHandlers): void
    {
        $this->validationHandlers = $validationHandlers;
    }

    public function setBusinessKey(bool $businessKey): void
    {
        $this->businessKey = $businessKey;
    }

    public function isBusinessKey(): bool
    {
        return $this->businessKey;
    }
}
