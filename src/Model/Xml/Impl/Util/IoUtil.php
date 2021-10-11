<?php

namespace BpmPlatform\Model\Xml\Impl\Util;

use BpmPlatform\Model\Xml\Impl\Instance\DomDocumentExt;
use BpmPlatform\Model\Xml\Instance\DomDocumentInterface;

class IoUtil
{
    /**
     * @param resource $file
     */
    public static function closeSilently($file): void
    {
        try {
            fclose($file);
        } catch (\Exception $e) {
            // ignored
        }
    }

    /**
     * @param resource $inputStream
     * @param bool $trim
     */
    public static function getStringFromInputStream($inputStream, bool $trim = true): ?string
    {
        if ($trim) {
            $meta = stream_get_meta_data($inputStream);
            $data = fread($inputStream, filesize($meta['uri']));
            return trim($data);
        } else {
            $meta = stream_get_meta_data($inputStream);
            return fread($inputStream, filesize($meta['uri']));
        }
    }

    /**
     * @param resource $outputStream
     */
    public static function convertOutputStreamToInputStream($outputStream): ?string
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
