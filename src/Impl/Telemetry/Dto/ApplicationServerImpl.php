<?php

namespace Jabe\Impl\Telemetry\Dto;

use Jabe\Impl\Util\ParseUtil;
use Jabe\Telemetry\ApplicationServerInterface;

class ApplicationServerImpl implements ApplicationServerInterface
{
    protected $vendor;
    protected $version;

    public function __construct(?string $vendorOrVersion, ?string $version = null)
    {
        $this->vendor = $version === null ? ParseUtil::parseServerVendor($vendorOrVersion) : $vendorOrVersion;
        $this->version = $version === null ? $vendorOrVersion : $version;
    }

    public function __toString()
    {
        return json_encode([
            'vendor' => $this->vendor,
            'version' => $this->version
        ]);
    }

    public function getVendor(): ?string
    {
        return $this->vendor;
    }

    public function setVendor(?string $vendor): void
    {
        $this->vendor = $vendor;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): void
    {
        $this->version = $version;
    }
}
