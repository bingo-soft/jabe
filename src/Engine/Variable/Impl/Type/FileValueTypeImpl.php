<?php

namespace BpmPlatform\Engine\Variable\Impl\Type;

use BpmPlatform\Engine\Variable\Type\FileValueTypeInterface;
use BpmPlatform\Engine\Variable\Value\{
    FileValueInterface,
    TypedValueInterface
};
use BpmPlatform\Engine\Variable\Variables;

class FileValueTypeImpl extends AbstractValueTypeImpl implements FileValueTypeInterface
{
    public function __construct()
    {
        parent::__construct("file");
    }

    public function createValue($stream, ?array $valueInfo = null): TypedValueInterface
    {
        if ($valueInfo == null) {
            throw new \InvalidArgumentException("Cannot create file without valueInfo.");
        }
        $filename = $valueInfo[self::VALUE_INFO_FILE_NAME];
        if ($filename == null) {
            throw new \InvalidArgumentException("Cannot create file without filename!");
        }
        $builder = Variables::fileValue($filename);
        if (file_exists($filename)) {
            $builder->file($stream);
            fclose($stream);
        } else {
            throw new \InvalidArgumentException("Provided value is not file");
        }

        if (array_key_exists(self::VALUE_INFO_FILE_MIME_TYPE, $valueInfo)) {
            $mimeType = $valueInfo[self::VALUE_INFO_FILE_MIME_TYPE];

            if ($mimeType == null) {
                throw new \InvalidArgumentException("The provided mime type is null");
            }

            $builder->mimeType($mimeType);
        }
        if (array_key_exists(self::VALUE_INFO_FILE_ENCODING, $valueInfo)) {
            $encoding = $valueInfo[self::VALUE_INFO_FILE_ENCODING];

            if ($encoding == null) {
                throw new \InvalidArgumentException("The provided encoding is null");
            }

            $builder->encoding($encoding);
        }

        $builder->setTransient($this->isTransient($valueInfo));
        return $builder->create();
    }

    public function getValueInfo(TypedValueInterface $typedValue): array
    {
        if (!($typedValue instanceof FileValueInterface)) {
            throw new \InvalidArgumentException("Value not of type FileValue");
        }
        $fileValue = $typedValue;
        $result = [];
        $result[self::VALUE_INFO_FILE_NAME] = $fileValue->getFilename();
        if ($fileValue->getMimeType() != null) {
            $result[self::VALUE_INFO_FILE_MIME_TYPE] = $fileValue->getMimeType();
        }
        if ($fileValue->getEncoding() != null) {
            $result[self::VALUE_INFO_FILE_ENCODING] = $fileValue->getEncoding();
        }
        if ($fileValue->isTransient()) {
            $result[self::VALUE_INFO_TRANSIENT] = $fileValue->isTransient();
        }
        return $result;
    }

    public function isPrimitiveValueType(): bool
    {
        return true;
    }
}
