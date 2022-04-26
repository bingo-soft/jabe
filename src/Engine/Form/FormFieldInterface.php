<?php

namespace Jabe\Engine\Form;

use Jabe\Engine\Variable\Value\TypedValueInterface;

interface FormFieldInterface
{
    /**
     * @return the Id of a form property. Must be unique for a given form.
     * The id is used for mapping the form field to a process variable.
     */
    public function getId(): string;

    /**
     * @return the human-readable display name of a form property.
     */
    public function getLabel(): string;

    /**
     * @return the type of this form field.
     */
    public function getType(): FormTypeInterface;

    /**
     * @return the name of the type of this form field
     */
    public function getTypeName(): string;

    /**
     * @return the value for this form field
     */
    public function getValue(): TypedValueInterface;

    /**
     * @return a list of {@link FormFieldValidationConstraint ValidationConstraints}.
     */
    public function getValidationConstraints(): array;

    public function addValidationConstraint(FormFieldValidationConstraintInterface $constraint): void;

    /**
     * @return a {@link Map} of additional properties. This map may be used for adding additional configuration
     * to a form field. An example may be layout hints such as the size of the rendered form field or information
     * about an icon to prepend or append to the rendered form field.
     */
    public function getProperties(): array;

    /**
     * @return bool true - if field is defined as businessKey, false otherwise
     */
    public function isBusinessKey(): bool;
}
