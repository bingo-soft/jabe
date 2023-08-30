<?php

namespace Jabe\Variable\Type;

interface FileValueTypeInterface extends ValueTypeInterface
{
    /**
     * Identifies the file's name as specified on value creation.
     */
    public const VALUE_INFO_FILE_NAME = "filename";

    /**
     * Identifies the file's mime type as specified on value creation.
     */
    public const VALUE_INFO_FILE_MIME_TYPE = "mimeType";

    /**
     * Identifies the file's encoding as specified on value creation.
     */
    public const VALUE_INFO_FILE_ENCODING = "encoding";
}
