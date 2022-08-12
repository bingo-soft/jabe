<?php

namespace Jabe\Impl\Form;

use Jabe\Form\FormFieldValidationConstraintInterface;

class FormFieldValidationConstraintImpl implements FormFieldValidationConstraintInterface, \Serializable
{
    protected $name;
    protected $configuration;

    public function __construct(string $name, string $configuration)
    {
        $this->name = $name;
        $this->configuration = $configuration;
    }

    public function serialize()
    {
        return json_encode([
            'name' => $this->name,
            'configuration' => serialize($this->configuration)
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->name = $json->name;
        $this->configuration = $json->configuration;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function setConfiguration(string $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
