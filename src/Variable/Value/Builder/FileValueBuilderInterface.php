<?php

namespace Jabe\Variable\Value\Builder;

interface FileValueBuilderInterface extends TypedValueBuilderInterface
{
    /**
     * Saves the MIME type of a file in the value infos.
     *
     * @param mimeType
     *          the MIME type as string
     */
    public function mimeType(?string $mimeType): FileValueBuilderInterface;

    /**
     * Sets the value to the specified File.
     */
    public function file($inputStream): FileValueBuilderInterface;

    /**
     * Sets the encoding for the file in the value infos (optional).
     *
     * @param encoding
     * @return
     */
    public function encoding(?string $encoding): FileValueBuilderInterface;
}
