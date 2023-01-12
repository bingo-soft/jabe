<?php

namespace Jabe\Impl\Util;

class ProcessEngineDetails
{
    public const EDITION_ENTERPRISE = "enterprise";
    public const EDITION_COMMUNITY = "community";

    protected $version;
    protected $edition;

    public function __construct(?string $version, ?string $edition)
    {
        $this->version = $version;
        $this->edition = $edition;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): void
    {
        $this->version = $version;
    }

    public function getEdition(): ?string
    {
        return $this->edition;
    }

    public function setEdition(?string $edition): void
    {
        $this->edition = $edition;
    }
}
