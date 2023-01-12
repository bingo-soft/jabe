<?php

namespace Jabe\Impl;

use Jabe\ProcessEngineException;
use Jabe\Impl\Db\Sql\DbSqlSessionFactory;
use Jabe\Impl\QueryOperator;
use Jabe\Impl\Variable\Serializer\{
    TypedValueSerializerInterface,
    ValueFieldsInterface,
    VariableSerializersInterface
};
use Jabe\Variable\Impl\Value\UntypedValueImpl;
use Jabe\Variable\Type\ValueType;
use Jabe\Variable\Value\TypedValueInterface;

class SingleQueryVariableValueCondition extends AbstractQueryVariableValueCondition implements ValueFieldsInterface
{
    protected $textValue;
    protected $textValue2;
    protected $longValue;
    protected $doubleValue;
    protected $type;
    protected $findNulledEmptyStrings;

    public function __construct(QueryVariableValue $variableValue)
    {
        parent::__construct($variableValue);
    }

    public function initializeValue(VariableSerializersInterface $serializers, ?TypedValueInterface $typedValue, ?string $dbType): void
    {
        $typedValue = $typedValue ?? $this->wrappedQueryValue->getTypedValue();

        $serializer = $this->determineSerializer($serializers, $typedValue);

        if ($typedValue instanceof UntypedValueImpl) {
            // type has been detected
            $typedValue = $serializer->convertToTypedValue($typedValue);
        }
        $serializer->writeValue($typedValue, $this);
        $this->type = $serializer->getName();
        /*if (ValueType::getString()->getName() == $this->type && DbSqlSessionFactory.ORACLE.equals(dbType)) {
            if ("".equals(textValue) && Arrays.asList(EQUALS, NOT_EQUALS).contains(wrappedQueryValue.getOperator())) {
                this.findNulledEmptyStrings = true;
            }
        }*/
    }


    protected function determineSerializer(VariableSerializersInterface $serializers, TypedValue $value): TypedValueSerializer
    {
        $serializer = $serializers->findSerializerForValue($value);

        if ($serializer->getType() == ValueType::getBytes()) {
            throw new ProcessEngineException("Variables of type ByteArray cannot be used to query");
        } elseif ($serializer->getType() == ValueType::getFile()) {
            throw new ProcessEngineException("Variables of type File cannot be used to query");
        } else {
            if (!$serializer->getType()->isPrimitiveValueType()) {
                throw new ProcessEngineException("Object values cannot be used to query");
            }
        }
        /* elseif ($serializer instanceof JPAVariableSerializer) {
            if (wrappedQueryValue.getOperator() != QueryOperator.EQUALS) {
                throw new ProcessEngineException("JPA entity variables can only be used in 'variableValueEquals'");
            }
        } */

        return $serializer;
    }

    public function getDisjunctiveConditions(): array
    {
        return [ $this ];
    }

    public function getName(): ?string
    {
        return $this->wrappedQueryValue->getName();
    }

    public function getTextValue(): ?string
    {
        return $this->textValue;
    }

    public function setTextValue(?string $textValue): void
    {
        $this->textValue = $textValue;
    }

    public function getTextValue2(): ?string
    {
        return $this->textValue2;
    }

    public function setTextValue2(?string $textValue2): void
    {
        $this->textValue2 = $textValue2;
    }

    public function getLongValue(): int
    {
        return $this->longValue;
    }

    public function setLongValue(int $longValue): void
    {
        $this->longValue = $longValue;
    }

    public function getDoubleValue(): float
    {
        return $this->doubleValue;
    }

    public function setDoubleValue(float $doubleValue): void
    {
        $this->doubleValue = $doubleValue;
    }

    public function getByteArrayValue(): ?string
    {
        return null;
    }

    public function setByteArrayValue($bytes): void
    {
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getFindNulledEmptyStrings(): bool
    {
        return $this->findNulledEmptyStrings;
    }

    public function setFindNulledEmptyStrings(bool $findNulledEmptyStrings): void
    {
        $this->findNulledEmptyStrings = $findNulledEmptyStrings;
    }
}
