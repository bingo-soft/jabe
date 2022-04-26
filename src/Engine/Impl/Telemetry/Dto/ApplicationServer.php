<?php

namespace Jabe\Engine\Impl\Telemetry\Dto;

use Jabe\Engine\Impl\Util\ParseUtil;

class ApplicationServer
{
    protected $vendor;
    protected $version;

    public function __construct(?string $vendor = null, string $version)
    {
        $this->vendor = $vendor ?? $this->parseServerVendor($version);
        $this->version = $version;
    }

    public function __toString()
    {
        return json_encode([
            'vendor' => $this->vendor,
            'version' => $this->version
        ]);
    }

    public function getVendor(): string
    {
        return $this->vendor;
    }

    public function setVendor(string $vendor): void
    {
        $this->vendor = $vendor;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }
}
