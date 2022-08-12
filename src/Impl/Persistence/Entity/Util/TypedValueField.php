<?php

namespace Jabe\Impl\Persistence\Entity\Util;

use Jabe\Application\{
    AbstractProcessApplication,
    ProcessApplicationInterface,
    ProcessApplicationReferenceInterface,
    ProcessApplicationUnavailableException
};
use Jabe\Impl\ProcessEngineLogger;
use Jabe\Impl\Context\Context;
use Jabe\Impl\Db\{
    DbEntityLifecycleAwareInterface,
    EnginePersistenceLogger
};
use Jabe\Impl\Interceptor\{
    CommandContext,
    CommandContextListenerInterface
};
use Jabe\Impl\Variable\Serializer\{
    TypedValueSerializerInterface,
    ValueFieldsInterface,
    ValueFieldsImpl,
    VariableSerializerFactoryInterface,
    VariableSerializersInterface
};
use Jabe\Variable\Impl\Value\UntypedValueImpl;
use Jabe\Variable\Type\ValueType;
use Jabe\Variable\Value\{
    SerializableValueInterface,
    TypedValueInterface
};

class TypedValueField implements DbEntityLifecycleAwareInterface, CommandContextListenerInterface
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;

    protected $serializerName;
    protected $serializer;

    protected $cachedValue;

    protected $errorMessage;

    protected $valueFields;

    protected $notifyOnImplicitUpdates = false;
    protected $updateListeners;

    public function __construct(ValueFieldsInterface $valueFields, bool $notifyOnImplicitUpdates)
    {
        $this->valueFields = $valueFields;
        $this->notifyOnImplicitUpdates = $notifyOnImplicitUpdates;
        $this->updateListeners = [];
    }

    public function getValue()
    {
        $typedValue = $this->getTypedValue(false);
        if ($typedValue !== null) {
            return $typedValue->getValue();
        } else {
            return null;
        }
    }

    public function getTypedValue(?bool $deserializeValue, bool $asTransientValue): TypedValueInterface
    {
        $deserializeValue = $deserializeValue ?? true;
        if (Context::getCommandContext() !== null) {
            // in some circumstances we must invalidate the cached value instead of returning it

            if ($this->cachedValue !== null && $this->cachedValue instanceof SerializableValue) {
                $serializableValue = $this->cachedValue;
                if ($deserializeValue && !$serializableValue->isDeserialized()) {
                    // clear cached value in case it is not deserialized and user requests deserialized value
                    $this->cachedValue = null;
                }
            }

            if ($this->cachedValue !== null && ($asTransientValue ^ $this->cachedValue->isTransient())) {
                // clear cached value if the value is not transient, but a transient value is requested
                $this->cachedValue = null;
            }
        }

        if ($this->cachedValue === null && $this->errorMessage === null) {
            try {
                $this->cachedValue = $this->getSerializer()->readValue($this->valueFields, $deserializeValue, $asTransientValue);

                if ($this->notifyOnImplicitUpdates && $this->isMutableValue($this->cachedValue)) {
                    Context::getCommandContext()->registerCommandContextListener($this);
                }
            } catch (\Exception $e) {
                // intercept the error message
                $this->errorMessage = $e->getMessage();
                throw $e;
            }
        }
        return $this->cachedValue;
    }

    public function setValue(TypedValueInterface $value): TypedValueInterface
    {
        // determine serializer to use
        $serializer = self::getSerializers()->findSerializerForValue(
            $value,
            Context::getProcessEngineConfiguration()->getFallbackSerializerFactory()
        );
        $serializerName = $serializer->getName();

        if ($value instanceof UntypedValueImpl) {
            // type has been detected
            $value = $serializer->convertToTypedValue($value);
        }

        // set new value
        $this->writeValue($value, $this->valueFields);

        // cache the value
        $this->cachedValue = $value;

        // ensure that we serialize the object on command context flush
        // if it can be implicitly changed
        if ($this->notifyOnImplicitUpdates && $this->isMutableValue($this->cachedValue)) {
            Context::getCommandContext()->registerCommandContextListener($this);
        }

        return $value;
    }

    public function isMutable(): bool
    {
        return $this->isMutableValue($this->cachedValue);
    }

    protected function isMutableValue(TypedValueInterface $value): bool
    {
        return $this->getSerializer()->isMutableValue($value);
    }

    protected function isValuedImplicitlyUpdated(): bool
    {
        if ($this->cachedValue !== null && $this->isMutableValue($this->cachedValue)) {
            $byteArray = $this->valueFields->getByteArrayValue();

            $tempValueFields = new ValueFieldsImpl();
            $this->writeValue($this->cachedValue, $tempValueFields);

            $byteArrayAfter = $tempValueFields->getByteArrayValue();

            return $byteArray != $byteArrayAfter;
        }
        return false;
    }

    protected function writeValue(TypedValueInterface $value, ValueFieldsInterface $valueFields): void
    {
        $this->getSerializer()->writeValue($value, $valueFields);
    }

    public function onCommandContextClose(CommandContext $commandContext): void
    {
        $this->notifyImplicitValueUpdate();
    }

    public function notifyImplicitValueUpdate(): void
    {
        if ($this->isValuedImplicitlyUpdated()) {
            foreach ($this->updateListeners as $typedValueImplicitUpdateListener) {
                $typedValueImplicitUpdateListener->onImplicitValueUpdate($this->cachedValue);
            }
        }
    }

    public function onCommandFailed(CommandContext $commandContext, \Throwable $t): void
    {
      // ignore
    }

    public function getSerializer(): TypedValueSerializerInterface
    {
        $this->ensureSerializerInitialized();
        return $this->serializer;
    }

    protected function ensureSerializerInitialized(): void
    {
        if ($this->serializerName !== null && $this->serializer === null) {
            $this->serializer = self::getSerializers()->getSerializerByName($this->serializerName);

            if ($this->serializer === null) {
                $this->serializer = self::getFallbackSerializer($this->serializerName);
            }

            if ($this->serializer === null) {
                //throw LOG.serializerNotDefinedException(this);
                throw new \Exception("serializerNotDefinedException");
            }
        }
    }

    public static function getSerializers(): ?VariableSerializersInterface
    {
        if (Context::getCommandContext() !== null) {
            $variableSerializers = Context::getProcessEngineConfiguration()->getVariableSerializers();
            $paSerializers = self::getCurrentPaSerializers();

            if ($paSerializers !== null) {
                return $variableSerializers->join($paSerializers);
            } else {
                return $variableSerializers;
            }
        } else {
            //throw LOG.serializerOutOfContextException();
            throw new \Exception("serializerOutOfContextException");
        }
    }

    public static function getFallbackSerializer(string $serializerName): ?TypedValueSerializerInterface
    {
        if (Context::getProcessEngineConfiguration() !== null) {
            $fallbackSerializerFactory = Context::getProcessEngineConfiguration()->getFallbackSerializerFactory();
            if ($fallbackSerializerFactory !== null) {
                return $fallbackSerializerFactory->getSerializer($serializerName);
            } else {
                return null;
            }
        } else {
            //throw LOG.serializerOutOfContextException();
            throw new \Exception("serializerOutOfContextException");
        }
    }

    protected static function getCurrentPaSerializers(): ?VariableSerializersInterface
    {
        if (Context::getCurrentProcessApplication() !== null) {
            $processApplicationReference = Context::getCurrentProcessApplication();
            try {
                $processApplicationInterface = $processApplicationReference->getProcessApplication();

                $rawPa = $processApplicationInterface->getRawObject();
                if ($rawPa instanceof AbstractProcessApplication) {
                    return $rawPa->getVariableSerializers();
                } else {
                    return null;
                }
            } catch (ProcessApplicationUnavailableException $e) {
                //throw LOG.cannotDeterminePaDataformats(e);
                throw $e;
            }
        } else {
            return null;
        }
    }

    public function getSerializerName(): string
    {
        return $this->serializerName;
    }

    public function setSerializerName(string $serializerName): void
    {
        $this->serializerName = $serializerName;
    }

    public function addImplicitUpdateListener(TypedValueUpdateListenerInterface $listener): void
    {
        $this->updateListeners[] = $listener;
    }

    /**
     * @return string the type name of the value
     */
    public function getTypeName(): string
    {
        if ($this->serializerName === null) {
            return ValueType::null()->getName();
        } else {
            return $this->getSerializer()->getType()->getName();
        }
    }

    /**
     * If the variable value could not be loaded, this returns the error message.
     *
     * @return an error message indicating why the variable value could not be loaded.
     */
    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function postLoad(): void
    {
    }

    public function clear(): void
    {
        $this->cachedValue = null;
    }
}
