<?php

namespace Jabe\Variable\Impl\Value\Builder;

use Jabe\Variable\Value\FileValueInterface;
use Jabe\Variable\Value\Builder\FileValueBuilderInterface;
use Jabe\Variable\Impl\Value\FileValueImpl;
use Jabe\Variable\Type\ValueType;

class FileValueBuilderImpl implements FileValueBuilderInterface
{
    protected $fileValue;

    public function __construct(?string $filename)
    {
        $this->fileValue = new FileValueImpl(ValueType::getFile(), $filename);
    }

    public function create(): FileValueInterface
    {
        return $this->fileValue;
    }

    public function mimeType(?string $mimeType): FileValueBuilderInterface
    {
        $this->fileValue->setMimeType($mimeType);
        return $this;
    }

    public function file($inputStream): FileValueBuilderInterface
    {
        if (is_resource($inputStream)) {
            $meta = stream_get_meta_data($inputStream);
            $data = fread($inputStream, filesize($meta['uri']));
            $this->fileValue->setValue($data);
            return $this;
        } else {
            throw new \InvalidArgumentException("Input is not of type file");
        }
    }

    public function encoding(?string $encoding): FileValueBuilderInterface
    {
        $this->fileValue->setEncoding($encoding);
        return $this;
    }

    public function setTransient(bool $isTransient): FileValueBuilderInterface
    {
        $this->fileValue->setTransient($isTransient);
        return $this;
    }
}
