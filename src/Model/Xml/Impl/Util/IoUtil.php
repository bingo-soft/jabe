<?php

namespace BpmPlatform\Model\Xml\Impl\Util;

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
        self::transformDocumentToXml($document, $source);
        return $source->saveXML();
    }

    public static function writeDocumentToOutputStream(DomDocumentInterface $document, string &$outputStream): void
    {
        self::transformDocumentToXml($document, $source);
        $outputStream = $source->saveXML();
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
