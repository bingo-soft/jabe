<?php

namespace BpmPlatform\Engine\Impl\Form;

use BpmPlatform\Engine\Form\FormRefInterface;

class FormRefImpl implements FormRefInterface
{
    private $key;
    private $binding;
    private $version;

    public function __construct(string $key, string $binding)
    {
        $this->key = $key;
        $this->binding = $binding;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function getBinding(): string
    {
        return $this->binding;
    }

    public function setBinding(string $binding): void
    {
        $this->binding = $binding;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): void
    {
        $this->version = $version;
    }

    public function __toString()
    {
        return "FormRefImpl [key=" . $this->key . ", binding=" . $this->binding . ", version=" . $this->version . "]";
    }
}
