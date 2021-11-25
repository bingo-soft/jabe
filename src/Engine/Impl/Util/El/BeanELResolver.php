<?php

namespace BpmPlatform\Engine\Impl\Util\El;

class BeanELResolver extends ELResolver
{
    private $readOnly;
    private $cache;
    private $defaultFactory;

    private static function findPublicAccessibleMethod(?\ReflectionMethod $method): ?\ReflectionMethod
    {
        if ($method != null && $method->isPublic()) {
            return $method;
        }
        return null;
    }

    private static function findAccessibleMethod(?\ReflectionMethod $method): ?\ReflectionMethod
    {
        //In PHP accessible method must be public
        return self::findPublicAccessibleMethod($method);
    }

    public function __construct(?bool $readOnly = false)
    {
        $this->readOnly = $readOnly;
        $this->cache = [];
    }

    public function getCommonPropertyType(?ELContext $context, $base): ?string
    {
        return $this->isResolvable($base) ? gettype($base) : null;
    }

    public function getFeatureDescriptors(?ELContext $context, $base): ?array
    {
        $ref = new \ReflectionClass(get_class($base));
        return $ref->getProperties();
    }

    public function getType(?ELContext $context, $base, $property)
    {
        if ($context == null) {
            throw new \Exception("Context is null");
        }
        $result = null;
        if ($this->isResolvable($base)) {
            $result = $this->toObjectProperty($base, $property)->getType();
            $context->setPropertyResolved(true);
        }
        return $result;
    }

    public function isReadOnly(?ELContext $context, $base, $property): bool
    {
        if ($context == null) {
            throw new \Exception("Context is null");
        }
        if ($this->isResolvable($base)) {
            $context->setPropertyResolved(true);
        }
        return $this->readOnly;
    }

    public function getValue(?ELContext $context, $base, $property)
    {
        if ($context == null) {
            throw new \Exception("Context is null");
        }
        $result = null;
        if ($this->isResolvable($base)) {
            $prop = $this->toObjectProperty($base, $property);
            if ($prop == null) {
                throw new PropertyNotFoundException("Cannot read property " . $property);
            }
            try {
                $result = $prop->getValue($base);
            } catch (\Exception $e) {
                throw new ELException("Unable to read object property");
            }
            $context->setPropertyResolved(true);
        }
        return $result;
    }

    public function setValue(?ELContext $context, $base, $property, $value): void
    {
        if ($context == null) {
            throw new \Exception("Context is null");
        }
        if ($this->isResolvable($base)) {
            if ($this->readOnly) {
                throw new PropertyNotWritableException("resolver is read-only");
            }
            $prop = $this->toObjectProperty($base, $property);
            if ($prop == null) {
                throw new PropertyNotFoundException("Cannot find property: " . $property);
            }
            try {
                $prop->setValue($base, $value);
            } catch (\Exception $e) {
                throw new PropertyNotWritableException("Cannot write property: " . $property);
            }
            $context->setPropertyResolved(true);
        }
    }

    public function invoke(?ELContext $context, $base, $method, ?array $paramTypes = [], ?array $params = [])
    {
        if ($context == null) {
            throw new NullPointerException();
        }
        $result = null;
        if ($this->isResolvable($base)) {
            $name = $method;
            $target = $this->findMethod($base, $name);
            if ($target == null) {
                throw new MethodNotFoundException("Cannot find method " . $name . " in " . get_class($base));
            }
            $result = $target->invoke($base, ...($this->coerceParams($this->getExpressionFactory($context), $target, $params)));
            $context->setPropertyResolved(true);
        }
        return $result;
    }

    private function findMethod($base, string $name): ?\ReflectionMethod
    {
        $ref = new \ReflectionClass(get_class($base));
        $method = null;
        try {
            $method = $ref->getMethod($name);
        } catch (\Exception $e) {
            throw new MethodNotFoundException("Cannot find method " . $name . " in " . get_class($base));
        }
        return self::findAccessibleMethod($method);
    }

    private function getExpressionFactory(ELContext $context): ?ExpressionFactory
    {
        $obj = $context->getContext(ExpressionFactory::class);
        if ($obj instanceof ExpressionFactory) {
            return $obj;
        }
        if ($this->defaultFactory == null) {
            $this->defaultFactory = ExpressionFactory::newInstance();
        }
        return $this->defaultFactory;
    }

    private function coerceParams(ExpressionFactory $factory, ReflectionMethod $method, array $parameters): array
    {
        $types = [];
        if (!empty($parameters)) {
            foreach ($parameters as $param) {
                $type = $param->getType();
                if ($type != null) {
                    $types[] = $type->getName();
                } else {
                    $types[] = "object";
                }
            }
        }
        $args = [];
        for ($i = 0; $i < count($parameters); $i++) {
            if ($types[$i] != "object" && gettype($parameters[$i]) != "object") {
                $args[] = $factory->coerceToType($parameters[$i], $types[$i]);
            } else {
                $args[] = $parameters[$i];
            }
        }
        return $args;
    }

    private function isResolvable($base): bool
    {
        return $base != null;
    }

    private function toObjectProperty($base, $property): \ReflectionProperty
    {
        $key = get_class($base);
        if (array_key_exists($key, $this->cache)) {
            $objectProperties = $this->cache[$key];
        } else {
            $objectProperties = new BeanProperties(get_class($base));
            $this->cache[$key] = $objectProperties;
        }
        $objectProperty = $property == null ? null : $objectProperties->getProperty($property);
        if ($objectProperty == null) {
            throw new PropertyNotFoundException("Could not find property " . $property . " in " . get_class($base));
        }
        return $objectProperty;
    }
}
