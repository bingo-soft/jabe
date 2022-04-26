<?php

namespace Jabe\Engine\Impl\Telemetry\Dto;

use Jabe\Engine\Impl\Util\JsonUtil;

class Data
{
    protected $installation;
    protected $product;

    public function __construct(string $installation, Product $product)
    {
        $this->installation = $installation;
        $this->product = $product;
    }

    public function __toString()
    {
        return json_encode([
            'installation' => $this->installation,
            'product' => json_decode($this->product)
        ]);
    }

    public function getInstallation(): string
    {
        return $this->installation;
    }

    public function setInstallation(string $installation): void
    {
        $this->installation = $installation;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): void
    {
        $this->product = $product;
    }

    public function mergeInternals(Internals $other): void
    {
        $product->getInternals()->mergeDynamicData($other);
    }
}
