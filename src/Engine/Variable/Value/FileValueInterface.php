<?php

namespace Jabe\Engine\Variable\Value;

interface FileValueInterface extends TypedValueInterface
{
    public function getFilename(): string;

    public function getMimeType(): ?string;

    /**
     * Convenience method to save the transformation. This method will perform no
     * check if the saved encoding is known and therefore could throw
     * every exception that {@link Charset#forName(String)} lists.
     * <p>
     * If no encoding has been saved it will return null.
     *
     */
    public function getEncodingAsCharset(): ?string;

    /**
     * @return string the saved encoding or null if none has been saved
     */
    public function getEncoding(): ?string;

    /**
     * @return resource
     */
    public function getValue();

    public function getByteArray(): ?string;
}
