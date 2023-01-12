<?php

namespace Jabe\Impl\Core\Model;

class CallableElementBinding
{
    public const LATEST = "latest";
    public const DEPLOYMENT = "deployment";
    public const VERSION = "version";
    public const VERSION_TAG = "versionTag";

    private $value;

    public function __construct(?string $value)
    {
        $this->value = $value;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }
}
