<?php

namespace BpmPlatform\Engine\Impl\Core\Variable;

use BpmPlatform\Engine\Impl\ProcessEngineLogger;
use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Core\CoreLogger;
use BpmPlatform\Engine\Impl\Persistence\Entity\Util\TypedValueField;
use BpmPlatform\Engine\Impl\Variable\Serializer\{
    TypedValueSerializerInterface,
    VariableSerializerFactoryInterface
};
use BpmPlatform\Engine\Variable\{
    VariableMapInterface,
    SerializationDataFormats
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
     * @param variableName
     * @param value
     */
    public static function checkPhpSerialization(string $variableName, TypedValueInterface $value): void
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

                if ($phpSerializationDataFormat == $requestedDataFormat) {
                    //throw CORE_LOGGER.phpSerializationProhibitedException(variableName);
                }
            }
        }
    }

    public static function setVariables(
        $variables,
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
}
