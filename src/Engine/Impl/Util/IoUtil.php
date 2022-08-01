<?php

namespace Jabe\Engine\Impl\Util;

use Jabe\Engine\Impl\ProcessEngineLogger;

class IoUtil
{
    //private static final EngineUtilLogger LOG = ProcessEngineLogger.UTIL_LOGGER;
    public static function readInputStream($inputStream, string $inputStreamName): string
    {
        try {
            $meta = stream_get_meta_data($inputStream);
            return fread($inputStream, filesize($meta['uri']));
        } catch (\Exception $e) {
            //throw LOG.exceptionWhileReadingStream(inputStreamName, e);
            throw new \Exception(sprintf("exceptionWhileReadingStream %s", $inputStreamName));
        }
    }

    public static function getFile(string $filePath)
    {
        if (file_exists($filePath)) {
            return fopen($filePath, 'r+');
        } else {
            throw new \Exception(sprintf("exceptionWhileGettingFilee %s", $filePath));
        }
    }

    public static function closeSilently($file): void
    {
        try {
            fclose($file);
        } catch (\Exception $e) {
            // ignored
        }
    }
}
