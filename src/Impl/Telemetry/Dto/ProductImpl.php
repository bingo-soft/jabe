<?php

namespace Jabe\Impl\Telemetry\Dto;

use Jabe\Telemetry\{
    InternalsInterface,
    ProductInterface
};

class ProductImpl implements ProductInterface
{
    protected $name;
    protected $version;
    protected $edition;
    protected $internals;

    public function __construct($nameOrProduct, string $version, string $edition, InternalsInterface $internals)
    {
        if (is_string($nameOrProduct)) {
            $this->name = $nameOrProduct;
            $this->version = $version;
            $this->edition = $edition;
            $this->internals = $internals;
        } elseif ($nameOrProduct instanceof ProductInterface) {
            $other = $nameOrProduct;
            $this->name = $other->name;
            $this->version = $other->version;
            $this->edition = $other->edition;
            $this->internals = new InternalsImpl($other->internals);
        }
    }

    public function __toString()
    {
        return json_encode([
            'name' => $this->name,
            'version' => $this->version,
            'edition' => $this->edition,
            'internals' => json_decode($this->internals)
        ]);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    public function getEdition(): string
    {
        return $this->edition;
    }

    public function setEdition(string $edition): void
    {
        $this->edition = $edition;
    }

    public function getInternals(): InternalsInterface
    {
        return $this->internals;
    }

    public function setInternals(InternalsInterface $internals): void
    {
        $this->internals = $internals;
    }
}
