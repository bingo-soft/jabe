<?php

namespace BpmPlatform\Engine\Application\Impl;

use BpmPlatform\Engine\Application\{
    ProcessApplicationInterface,
    ProcessApplicationReferenceInterface
};

class ProcessApplicationIdentifier
{
    protected $name;
    protected $reference;
    protected $processApplication;

    public function __construct($obj)
    {
        if ($obj instanceof ProcessApplicationReferenceInterface) {
            $this->reference = $obj;
        } elseif ($obj instanceof ProcessApplicationInterface) {
            $this->processApplication = $obj;
        } elseif (is_string($obj)) {
            $this->name = $obj;
        }
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getReference(): ?ProcessApplicationReferenceInterface
    {
        return $this->reference;
    }

    public function getProcessApplication(): ?ProcessApplicationInterface
    {
        return $this->processApplication;
    }

    public function __toString()
    {
        $paName = $this->name;
        if ($paName == null && $this->reference != null) {
            $paName = $this->reference->getName();
        }
        if ($paName == null && $this->processApplication != null) {
            $paName = $this->processApplication->getName();
        }
        return "ProcessApplicationIdentifier[name=" . $paName . "]";
    }
}
