<?php

namespace Jabe\Engine\Impl\Form\Handler;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Delegate\{
    ExpressionInterface,
    VariableScopeInterface
};
use Jabe\Engine\Form\{
    FormPropertyInterface,
    FormTypeInterface
};
use Jabe\Engine\Impl\El\StartProcessVariableScope;
use Jabe\Engine\Impl\Form\FormPropertyImpl;
use Jabe\Engine\Impl\Form\Type\AbstractFormFieldType;
use Jabe\Engine\Impl\Persistence\Entity\ExecutionEntity;
use Jabe\Engine\Variable\VariableMapInterface;

class FormPropertyHandler
{
    protected $id;
    protected $name;
    protected $type;
    protected $isReadable;
    protected $isWritable;
    protected $isRequired;
    protected $variableName;
    protected $variableExpression;
    protected $defaultExpression;

    public function createFormProperty(?ExecutionEntity $execution): FormPropertyInterface
    {
        $formProperty = new FormPropertyImpl($this);
        $modelValue = null;

        if ($execution !== null) {
            if ($this->variableName !== null || $this->variableExpression === null) {
                $varName = $this->variableName !== null ? $this->variableName : $this->id;
                if ($execution->hasVariable($varName)) {
                    $modelValue = $execution->getVariable($varName);
                } elseif ($this->defaultExpression !== null) {
                    $modelValue = $this->defaultExpression->getValue($execution);
                }
            } else {
                $modelValue = $this->variableExpression->getValue($execution);
            }
        } else {
            // Execution is null, the form-property is used in a start-form. Default value
            // should be available (ACT-1028) even though no execution is available.
            if ($this->defaultExpression !== null) {
                $modelValue = $this->defaultExpression->getValue(StartProcessVariableScope::getSharedInstance());
            }
        }

        if (is_string($modelValue)) {
            $formProperty->setValue($modelValue);
        } elseif ($this->type !== null) {
            $formValue = $this->type->convertModelValueToFormValue($modelValue);
            $formProperty->setValue($formValue);
        } elseif ($modelValue !== null) {
            $formProperty->setValue(strval($modelValue));
        }

        return $formProperty;
    }

    public function submitFormProperty(VariableScopeInterface $variableScope, VariableMapInterface $variables): void
    {
        if (!$this->isWritable && $variables->containsKey($this->id)) {
            throw new ProcessEngineException("form property '" . $this->id . "' is not writable");
        }

        if ($this->isRequired && !$variables->containsKey($this->id) && $this->defaultExpression === null) {
            throw new ProcessEngineException("form property '" . $this->id . "' is required");
        }

        $modelValue = null;
        if ($variables->containsKey($this->id)) {
            $propertyValue = $variables->remove($this->id);
            if ($this->type !== null) {
                $modelValue = $this->type->convertFormValueToModelValue($propertyValue);
            } else {
                $modelValue = $propertyValue;
            }
        } elseif ($this->defaultExpression !== null) {
            $expressionValue = $this->defaultExpression->getValue($variableScope);
            if ($this->type !== null && $expressionValue !== null) {
                $modelValue = $this->type->convertFormValueToModelValue(strval($expressionValue));
            } elseif ($expressionValue !== null) {
                $modelValue = strval($expressionValue);
            } elseif ($this->isRequired) {
                throw new ProcessEngineException("form property '" . $this->id . "' is required");
            }
        }

        if ($modelValue !== null) {
            if ($this->variableName !== null) {
                $variableScope->setVariable($this->variableName, $modelValue);
            } elseif ($this->variableExpression !== null) {
                $this->variableExpression->setValue($modelValue, $variableScope);
            } else {
                $variableScope->setVariable($this->id, $modelValue);
            }
        }
    }

    // getters and setters //////////////////////////////////////////////////////

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): FormTypeInterface
    {
        return $this->type;
    }

    public function setType(AbstractFormFieldType $type): void
    {
        $this->type = $type;
    }

    public function isReadable(): bool
    {
        return $this->isReadable;
    }

    public function setReadable(bool $isReadable): void
    {
        $this->isReadable = $isReadable;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function setRequired(bool $isRequired): void
    {
        $this->isRequired = $isRequired;
    }

    public function getVariableName(): string
    {
        return $this->variableName;
    }

    public function setVariableName(string $variableName): void
    {
        $this->variableName = $variableName;
    }

    public function getVariableExpression(): ?ExpressionInterface
    {
        return $this->variableExpression;
    }

    public function setVariableExpression(ExpressionInterface $variableExpression): void
    {
        $this->variableExpression = $variableExpression;
    }

    public function getDefaultExpression(): ?ExpressionInterface
    {
        return $this->defaultExpression;
    }

    public function setDefaultExpression(ExpressionInterface $defaultExpression): void
    {
        $this->defaultExpression = $defaultExpression;
    }

    public function isWritable(): bool
    {
        return $this->isWritable;
    }

    public function setWritable(bool $isWritable): void
    {
        $this->isWritable = $isWritable;
    }
}
