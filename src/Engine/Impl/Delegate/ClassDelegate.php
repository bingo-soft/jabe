<?php

namespace Jabe\Engine\Impl\Delegate;

abstract class ClassDelegate
{
    protected $className;
    protected $fieldDeclarations;

    public function __construct(string $className, array $fieldDeclarations)
    {
        $this->className = $className;
        $this->fieldDeclarations = $fieldDeclarations;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getFieldDeclarations(): array
    {
        return $this->fieldDeclarations;
    }
}
