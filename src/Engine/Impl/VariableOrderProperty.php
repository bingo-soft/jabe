<?php

namespace Jabe\Engine\Impl;

use Jabe\Engine\ProcessEngineException;
use Jabe\Engine\Variable\Type\{
    ValueType,
    ValueTypeInterface
};

class VariableOrderProperty extends QueryOrderingProperty
{
    public function __construct(string $name = null, ValueTypeInterface $valueType = null)
    {
        if ($name !== null && $valueType !== null) {
            parent::__construct(QueryOrderingProperty::RELATION_VARIABLE, $this->typeToQueryProperty($valueType));
            $this->relationConditions = [];
            $this->relationConditions[] = new QueryEntityRelationCondition(VariableInstanceQueryProperty::variableName(), $name);
            // works only for primitive types
            $this->relationConditions[] = new QueryEntityRelationCondition(VariableInstanceQueryProperty::variableType(), $valueType->getName());
        }
    }

    public static function forProcessInstanceVariable(string $variableName, ValueTypeInterface $valueType): VariableOrderProperty
    {
        $orderingProperty = new VariableOrderProperty($variableName, $valueType);
        $orderingProperty->relationConditions[] =
            new QueryEntityRelationCondition(VariableInstanceQueryProperty::executionId(), TaskQueryProperty::processInstanceId())
        ;

        return $orderingProperty;
    }

    public static function forExecutionVariable(string $variableName, ValueTypeInterface $valueType): VariableOrderProperty
    {
        $orderingProperty = new VariableOrderProperty($variableName, $valueType);
        $orderingProperty->relationConditions[] =
            new QueryEntityRelationCondition(VariableInstanceQueryProperty::executionId(), TaskQueryProperty::executionId())
        ;

        return $orderingProperty;
    }

    public static function forTaskVariable(string $variableName, ValueTypeInterface $valueType): VariableOrderProperty
    {
        $orderingProperty = new VariableOrderProperty($variableName, $valueType);
        $orderingProperty->relationConditions[] =
            new QueryEntityRelationCondition(VariableInstanceQueryProperty::taskId(), TaskQueryProperty::taskId());

        return $orderingProperty;
    }

    /*public static function forCaseInstanceVariable(string $variableName, ValueTypeInterface $valueType): VariableOrderProperty
    {
        $orderingProperty = new VariableOrderProperty($variableName, $valueType);
        $orderingProperty->relationConditions[] =
            new QueryEntityRelationCondition(VariableInstanceQueryProperty.CASE_EXECUTION_ID, TaskQueryProperty.CASE_INSTANCE_ID)
        );

        return orderingProperty;
    }

    public static VariableOrderProperty forCaseExecutionVariable(string $variableName, ValueTypeInterface valueType) {
        VariableOrderProperty orderingProperty = new VariableOrderProperty(variableName, valueType);
        orderingProperty.relationConditions.add(
            new QueryEntityRelationCondition(VariableInstanceQueryProperty.CASE_EXECUTION_ID, TaskQueryProperty.CASE_EXECUTION_ID));

        return orderingProperty;
    }*/

    public static function typeToQueryProperty(ValueTypeInterface $type): QueryPropertyImpl
    {
        if (ValueType::getString() == $type) {
            return VariableInstanceQueryProperty::textAsLower();
        } elseif (ValueType::getInteger() == $type) {
            return VariableInstanceQueryProperty::long();
        } elseif (ValueType::getDate() == $type) {
            return VariableInstanceQueryProperty::long();
        } elseif (ValueType::getBoolean() == $type) {
            return VariableInstanceQueryProperty::long();
        } elseif (ValueType::getLong() == $type) {
            return VariableInstanceQueryProperty::long();
        } elseif (ValueType::getDouble() == $type) {
            return VariableInstanceQueryProperty::double();
        } else {
            throw new ProcessEngineException("Cannot order by variables of type " . $type->getName());
        }
    }
}
