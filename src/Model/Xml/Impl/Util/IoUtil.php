<?php

namespace BpmPlatform\Model\Xml\Impl\Util;

use BpmPlatform\Model\Xml\Impl\Instance\DomDocumentExt;
use BpmPlatform\Model\Xml\Instance\DomDocumentInterface;

class IoUtil
{
    public static function closeSilently(resource $file): void
    {
        try {
            fclose($file);
        } catch (\Exception $e) {
            // ignored
        }
    }

    public static function getStringFromInputStream(?string $inputStream, bool $trim = true): ?string
    {
        if ($trim) {
            return trim($inputStream);
        } else {
            return $inputStream;
        }
    }

    public static function convertOutputStreamToInputStream(?string $outputStream): ?string
    {
        return $outputStream;
    }

    public static function convertXmlDocumentToString(DomDocumentInterface $document): string
    {
        $source = new DomDocumentExt();
        self::transformDocumentToXml($document, $source);
        return $source->saveXML();
    }

    /**
     * @param DomDocumentInterface $document
     * @param resource $stream
     */
    public static function writeDocumentToOutputStream(DomDocumentInterface $document, $stream): void
    {
        $source = new DomDocumentExt();
        self::transformDocumentToXml($document, $source);
        fwrite($stream, $source->saveXML());
    }

    /**
     * @param DomDocumentInterface $document
     * @param mixed $result
     */
    public static function transformDocumentToXml(DomDocumentInterface $document, &$result): void
    {
        $result = $document->getDomSource();
    }
}
