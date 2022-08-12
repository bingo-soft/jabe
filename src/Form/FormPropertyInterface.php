<?php

namespace Jabe\Form;

interface FormPropertyInterface
{
    /** The key used to submit the property in {@link FormService#submitStartFormData(String, java.util.Map)}
     * or {@link FormService#submitTaskFormData(String, java.util.Map)} */
    public function getId(): string;

    /** The display label */
    public function getName(): string;

    /** Type of the property. */
    public function getType(): FormTypeInterface;

    /** Optional value that should be used to display in this property */
    public function getValue(): string;

    /** Is this property read to be displayed in the form and made accessible with the methods
     * FormService#getStartFormData(String) and FormService#getTaskFormData(String). */
    public function isReadable(): bool;

    /** Is this property expected when a user submits the form? */
    public function isWritable(): bool;

    /** Is this property a required input field */
    public function isRequired(): bool;
}
