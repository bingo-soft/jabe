<?php

namespace Jabe\Engine\Impl\Telemetry\Dto;

use Jabe\Engine\Impl\Util\JsonUtil;
use Jabe\Engine\Telemetry\{
    InternalsInterface,
    ProductInterface,
    TelemetryDataInterface
};

class TelemetryDataImpl implements TelemetryDataInterface
{
    protected $installation;
    protected $product;

    public function __construct($installationOrOther, ProductInterface $product = null)
    {
        if (is_string($installationOrOther) && $product !== null) {
            $this->installation = $installationOrOther;
            $this->product = $product;
        } elseif ($installationOrOther instanceof TelemetryDataInterface) {
            $this->installation = $installationOrOther->installation;
            $this->product = new ProductImpl($installationOrOther->product);
        }
    }

    public function getInstallation(): string
    {
        return $this->installation;
    }

    public function setInstallation(string $installation): void
    {
        $this->installation = $installation;
    }

    public function getProduct(): ProductInterface
    {
        return $this->product;
    }

    public function setProduct(ProductInterface $product): void
    {
        $this->product = $product;
    }

    public function mergeInternals(InternalsInterface $other): void
    {
        $this->product->getInternals()->mergeDynamicData($other);
    }

    public function __toString()
    {
        return JsonUtil::asString($this);
    }
}
