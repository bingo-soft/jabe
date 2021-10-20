<?php

namespace BpmPlatform\Engine\Impl\Telemetry\Dto;

class Product
{
    protected $name;
    protected $version;
    protected $edition;
    protected $internals;

    public function __construct(string $name, string $version, string $edition, Internals $internals)
    {
        $this->name = $name;
        $this->version = $version;
        $this->edition = $edition;
        $this->internals = $internals;
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

    public function getInternals(): Internals
    {
        return $this->internals;
    }

    public function setInternals(Internals $internals): void
    {
        $this->internals = $internals;
    }
}
