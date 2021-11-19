<?php

namespace BpmPlatform\Engine\Impl\Core\Variable;

use BpmPlatform\Engine\Impl\ProcessEngineLogger;
use BpmPlatform\Engine\Impl\Cmd\CommandLogger;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Core\CoreLogger;
use BpmPlatform\Engine\Impl\Interceptor\CommandContext;
use BpmPlatform\Engine\Impl\Persistence\Entity\VariableInstanceEntity;
use BpmPlatform\Engine\Impl\Persistence\Entity\Util\TypedValueField;
use BpmPlatform\Engine\Impl\Variable\Serializer\{
    TypedValueSerializerInterface,
    VariableSerializerFactoryInterface
};
use BpmPlatform\Engine\Variable\{
    SerializationDataFormats,
    VariableMapInterface,
    Variables
};
use BpmPlatform\Engine\Variable\Value\{
    SerializableValueInterface,
    TypedValueInterface
};

class VariableUtil
{
    //public static CoreLogger CORE_LOGGER = ProcessEngineLogger.CORE_LOGGER;

    /**
     * Checks, if PHP serialization will be used and if it is allowed to be used.
     * @param value
     */
    public static function isPhpSerializationProhibited(TypedValueInterface $value): bool
    {
        $processEngineConfiguration = Context::getProcessEngineConfiguration();

        if (
            $value instanceof SerializableValueInterface &&
            !$processEngineConfiguration->isPhpSerializationFormatEnabled()
        ) {
            $serializableValue = $value;

            // if PHP serialization is prohibited
            if (!$serializableValue->isDeserialized()) {
                $phpSerializationDataFormat = SerializationDataFormats::PHP;
                $requestedDataFormat = $serializableValue->getSerializationDataFormat();

                if ($requestedDataFormat == null) {
                    $fallbackSerializerFactory = $processEngineConfiguration->getFallbackSerializerFactory();

                    // check if PHP serializer will be used
                    $serializerForValue = TypedValueField::getSerializers()
                        ->findSerializerForValue($serializableValue, $fallbackSerializerFactory);
                    if ($serializerForValue != null) {
                        $requestedDataFormat = $serializerForValue->getSerializationDataformat();
                    }
                }

                return $phpSerializationDataFormat == $requestedDataFormat;
            }
        }

        return false;
    }

    public static function checkPhpSerialization(string $variableName, TypedValueInterface $value)
    {
        if (self::isPhpSerializationProhibited($value)) {
            //throw CORE_LOGGER.javaSerializationProhibitedException(variableName);
        }
    }

    public static function setVariables(
        &$variables,
        SetVariableFunctionInterface $setVariableFunction
    ): void {
        if (empty($variables)) {
            if ($variables instanceof VariableMapInterface) {
                foreach (array_keys($variables->asValueMap()) as $variableName) {
                    $value = $variables->getValueTyped($variableName);
                    $setVariableFunction->apply($variableName, $value);
                }
            } elseif (is_array($variables)) {
                foreach ($variables as $variableName => $value) {
                    $setVariableFunction->apply($variableName, $value);
                }
            }
        }
    }

    public static function setVariablesByBatchId(&$variables, string $batchId): void
    {
        self::setVariables($variables, new class ($batchId) implements SetVariableFunctionInterface {
            private $batchId;

            public function __construct(string $batchId)
            {
                $this->batchId = $batchId;
            }

            public function apply(string $variableName, $variableValue): void
            {
                VariableUtil::setVariableByBatchId($this->batchId, $variableName, $variableValue);
            }
        });
    }

    public static function setVariableByBatchId(string $batchId, string $variableName, $variableValue): void
    {
        $variableTypedValue = Variables::untypedValue($variableValue);

        $isTransient = $variableTypedValue->isTransient();
        if ($isTransient) {
            //throw CMD_LOGGER.exceptionSettingTransientVariablesAsyncNotSupported(variableName);
        }

        self::checkPhpSerialization($variableName, $variableTypedValue);

        $variableInstance = VariableInstanceEntity::createAndInsert($variableName, $variableTypedValue);

        $variableInstance->setVariableScopeId($batchId);
        $variableInstance->setBatchId($batchId);
    }

    public static function findBatchVariablesSerialized(string $batchId, CommandContext $commandContext): array
    {
        $variableInstances = $commandContext->getVariableInstanceManager()->findVariableInstancesByBatchId($batchId);
        $result = [];
        foreach ($variableInstances as $variableInstance) {
            $result[VariableInstanceEntity::getName($variableInstance)] = VariableUtil::getSerializedValue($variableInstance);
        }
        return $result;
    }

    protected static function getSerializedValue(VariableInstanceEntity $variableInstanceEntity): TypedValueInterface
    {
        return $variableInstanceEntity->getTypedValue(false);
    }
}
