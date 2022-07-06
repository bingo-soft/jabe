<?php

namespace Jabe\Engine\Impl\Variable\Serializer;

use Jabe\Engine\Variable\Variables;
use Jabe\Engine\Variable\Impl\Value\{
    FileValueImpl,
    UntypedValueImpl
};
use Jabe\Engine\Variable\Type\ValueType;
use Jabe\Engine\Variable\Value\{
    FileValueInterface,
    TypedValueInterface
};
use Jabe\Engine\Variable\Value\FileValueBuiilderInterface;

class FileValueSerializer extends PrimitiveValueSerializer
{
    /**
     * The numbers values we encoded in textfield two.
     */
    protected const NR_OF_VALUES_IN_TEXTFIELD2 = 2;

    /**
     * The separator to be able to store encoding and mimetype inside the same
     * text field. Please be aware that the separator only works when it is a
     * character that is not allowed in the first component.
     */
    protected const MIMETYPE_ENCODING_SEPARATOR = "#";

    public function __construct()
    {
        parent::__construct(ValueType::getFile());
    }

    public function writeValue(FileValueInterface $value, ValueFieldsInterface $valueFields): void
    {
        $data = $value->getByteArray();
        $valueFields->setByteArrayValue($data);
        $valueFields->setTextValue($value->getFilename());
        if ($value->getMimeType() === null && $value->getEncoding() !== null) {
            $valueFields->setTextValue2(self::MIMETYPE_ENCODING_SEPARATOR . $value->getEncoding());
        } elseif ($value->getMimeType() !== null && $value->getEncoding() === null) {
            $valueFields->setTextValue2($value->getMimeType() . self::MIMETYPE_ENCODING_SEPARATOR);
        } elseif ($value->getMimeType() !== null && $value->getEncoding() !== null) {
            $valueFields->setTextValue2($value->getMimeType() . self::MIMETYPE_ENCODING_SEPARATOR . $value->getEncoding());
        }
    }

    public function convertToTypedValue(UntypedValueImpl $untypedValue): FileValueInterface
    {
        throw new \Exception("Currently no automatic conversation from UntypedValue to FileValue");
    }

    public function readValue(ValueFieldsInterface $valueFields, bool $isTransient, bool $deserializeValue = false): FileValueInterface
    {
        $fileName = $valueFields->getTextValue();
        if ($fileName === null) {
            // ensure file name is not null
            $fileName = "";
        }
        $builder = Variables::fileValue($fileName);
        if ($valueFields->getByteArrayValue() !== null) {
            $builder->file($valueFields->getByteArrayValue());
        }
        // to ensure the same array size all the time
        if ($valueFields->getTextValue2() !== null) {
            $split = explode(self::MIMETYPE_ENCODING_SEPARATOR, $valueFields->getTextValue2(), self::NR_OF_VALUES_IN_TEXTFIELD2);

            $mimeType = count($split) == 2 ? $split[0] : null;
            $encoding = count($split) == 1 ? $split[0] : null;

            $builder->mimeType($mimeType);
            $builder->encoding($encoding);
        }

        $builder->setTransient($asTransientValue);

        return $builder->create();
    }

    public function getName(): string
    {
        return $this->valueType->getName();
    }

    protected function canWriteValue(?TypedValueInterface $value): bool
    {
        if ($value === null || $value->getType() === null) {
            // untyped value
            return false;
        }
        return $value->getType()->getName() == $this->getName();
    }
}
