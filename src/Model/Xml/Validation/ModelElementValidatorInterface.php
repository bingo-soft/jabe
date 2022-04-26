<?php

namespace Jabe\Model\Xml\Validation;

interface ModelElementValidatorInterface
{
    /**
     * @return mixed
     */
    public function getElementType();

    /**
     * @param mixed $element
     */
    public function validate($element, ValidationResultCollectorInterface $validationResultCollector): void;
}
