<?php

namespace Jabe\Engine\Impl\Juel;

use Jabe\Engine\Impl\Util\El\{
    ArrayELResolver,
    CompositeELResolver,
    ELContext,
    ELResolver,
    ListELResolver,
    MapELResolver
};

class SimpleResolver extends ELResolver
{
    private static $DEFAULT_RESOLVER_READ_ONLY;

    private static $DEFAULT_RESOLVER_READ_WRITE;

    private $root;
    private $delegate;

    /**
     * Create a read/write resolver capable of resolving top-level identifiers. Everything else is
     * passed to the supplied delegate.
     */
    public function __construct(?ELResolver $resolver = null, bool $readOnly = false)
    {
        if (self::$DEFAULT_RESOLVER_READ_ONLY === null) {
            self::$DEFAULT_RESOLVER_READ_ONLY = new CompositeELResolver();
            self::$DEFAULT_RESOLVER_READ_ONLY->add(new ArrayELResolver(true));
            self::$DEFAULT_RESOLVER_READ_ONLY->add(new ListELResolver(true));
            self::$DEFAULT_RESOLVER_READ_ONLY->add(new MapELResolver(true));
        }
        if (self::$DEFAULT_RESOLVER_READ_WRITE === null) {
            self::$DEFAULT_RESOLVER_READ_WRITE = new CompositeELResolver();
            self::$DEFAULT_RESOLVER_READ_WRITE->add(new ArrayELResolver(false));
            self::$DEFAULT_RESOLVER_READ_WRITE->add(new ListELResolver(false));
            self::$DEFAULT_RESOLVER_READ_WRITE->add(new MapELResolver(false));
        }
        $resolver = $resolver ?? ($readOnly ? self::$DEFAULT_RESOLVER_READ_ONLY : self::$DEFAULT_RESOLVER_READ_WRITE);
        $this->delegate = new CompositeELResolver();
        $this->root = new RootPropertyResolver($readOnly);
        $this->delegate->add($this->root);
        $this->delegate->add($resolver);
    }

    /**
     * Answer our root resolver which provides an API to access top-level properties.
     *
     * @return root property resolver
     */
    public function getRootPropertyResolver(): RootPropertyResolver
    {
        return $this->root;
    }

    public function getCommonPropertyType(?ELContext $context, $base): ?string
    {
        return $this->delegate->getCommonPropertyType($context, $base);
    }

    public function getFeatureDescriptors(?ELContext $context, $base): ?array
    {
        return $this->delegate->getFeatureDescriptors($context, $base);
    }

    public function getType(?ELContext $context, $base, $property): ?string
    {
        return $this->delegate->getType($context, $base, $property);
    }

    public function getValue(?ELContext $context, $base, $property)
    {
        return $this->delegate->getValue($context, $base, $property);
    }

    public function isReadOnly(?ELContext $context, $base, $property): bool
    {
        return $this->delegate->isReadOnly($context, $base, $property);
    }

    public function setValue(?ELContext $context, $base, $property, $value): void
    {
        $this->delegate->setValue($context, $base, $property, $value);
    }

    public function invoke(?ELContext $context, $base, $method, ?array $paramTypes = [], ?array $params = [])
    {
        return $this->delegate->invoke($context, $base, $method, $paramTypes, $params);
    }
}
