<?php

namespace Jabe\Engine\Impl\Util;

class ConnectUtil
{
    // request
    public const PARAM_NAME_REQUEST_URL = "url";
    public const PARAM_NAME_REQUEST_METHOD = "method";
    public const PARAM_NAME_REQUEST_PAYLOAD = "payload";
    public const PARAM_NAME_REQUEST_CONFIG = "request-config";

    // request methods
    public const METHOD_NAME_POST = "POST";

    // config options
    public const CONFIG_NAME_CONNECTION_TIMEOUT = "connection-timeout";
    public const CONFIG_NAME_SOCKET_TIMEOUT = "socket-timeout";

    // response
    public const PARAM_NAME_RESPONSE_STATUS_CODE = "statusCode";
    public const PARAM_NAME_RESPONSE = "response";

    // common between request and response
    public const PARAM_NAME_HEADERS = "headers";
    public const HEADER_CONTENT_TYPE = "Content-Type";

    // helper methods
    public static function assembleRequestParameters(
        string $methodName,
        string $url,
        string $contentType,
        string $payload
    ): array {
        $requestHeaders = [];
        $requestHeaders[self::HEADER_CONTENT_TYPE] = $contentType;

        $requestParams = [];
        $requestParams[self::PARAM_NAME_REQUEST_METHOD] = $methodName;
        $requestParams[self::PARAM_NAME_REQUEST_URL] = $url;
        $requestParams[self::PARAM_NAME_HEADERS] = $requestHeaders;
        $requestParams[self::PARAM_NAME_REQUEST_PAYLOAD] = $payload;

        return $requestParams;
    }

    public static function addRequestTimeoutConfiguration(array $requestParams, int $timeout): array
    {
        $config = [];
        $config[self::CONFIG_NAME_CONNECTION_TIMEOUT] = $timeout;
        $config[self::CONFIG_NAME_SOCKET_TIMEOUT] = $timeout;

        $requestParams[self::PARAM_NAME_REQUEST_CONFIG] = $config;

        return $requestParams;
    }
}
