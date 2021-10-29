<?php

namespace BpmPlatform\Engine\Impl\Language;

use BpmPlatform\Engine\Impl\Expression\{
    ELContext,
    ELException,
    ValueExpression
};

class ObjectValueExpression extends ValueExpression
{
    private $converter;
    private $object;
    private $type;

    /**
     * Wrap an object into a value expression.
     * @param converter type converter
     * @param object the object to wrap
     * @param type the expected type this object will be coerced in {@link #getValue(ELContext)}.
     */
    public function __construct(TypeConverter $converter, $object = null, ?string $type = null)
    {
        parent::__construct();

        $this->converter = $converter;
        $this->object = $object;
        $this->type = $type;

        if ($type == null) {
            throw new \Exception(LocalMessages::get("error.value.notype"));
        }
    }

    public function serialize()
    {
        return json_encode([
            'converter' => serialize($this->converter),
            'object' => $this->object,
            'type' => $this->type
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->converter = unserialize($json->converter);
        $this->object = $json->object;
        $this->type = $json->type;
    }

    /**
     * Two object value expressions are equal if and only if their wrapped objects are equal.
     */
    public function equals($obj): bool
    {
        if ($obj != null && get_class($obj) == get_class($this)) {
            if ($type != $obj->type) {
                return false;
            }
            return $this->object == $obj->object || $object != null && $object == $obj->object;
        }
        return false;
    }

    /**
     * Answer the wrapped object, coerced to the expected type.
     */
    public function getValue(ELContext $context)
    {
        return $this->converter->convert($this->object, $type);
    }

    /**
     * Answer <code>null</code>.
     */
    public function getExpressionString(): ?string
    {
        return null;
    }

    /**
     * Answer <code>false</code>.
     */
    public function isLiteralText(): bool
    {
        return false;
    }

    /**
     * Answer <code>null</code>.
     */
    public function getType(ELContext $context): ?string
    {
        return null;
    }

    /**
     * Answer <code>true</code>.
     */
    public function isReadOnly(ELContext $context): bool
    {
        return true;
    }

    /**
     * Throw an exception.
     */
    public function setValue(ELContext $context, $value): void
    {
        throw new ELException(LocalMessages::get("error.value.set.rvalue", "<object value expression>"));
    }

    public function __toString()
    {
        return "ValueExpression(" . $this->object . ")";
    }

    public function getExpectedType(): string
    {
        return $this->type;
    }
}
