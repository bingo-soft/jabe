<?php

namespace Jabe\Model\Xml\Impl\Validation;

use Jabe\Model\Xml\Impl\ModelInstanceImpl;
use Jabe\Model\Xml\Validation\ValidationResultsInterface;

class ModelInstanceValidator
{
    protected $modelInstanceImpl;
    private $validators;

    public function __construct(ModelInstanceImpl $modelInstanceImpl, array $validators)
    {
        $this->modelInstanceImpl = $modelInstanceImpl;
        $this->validators = $validators;
    }

    public function validate(): ValidationResultsInterface
    {
        $resultCollector = new ValidationResultsCollectorImpl();

        foreach ($this->validators as $validator) {
            $elementType = $validator->getElementType();
            $modelElementsByType = $modelInstanceImpl->getModelElementsByType($elementType);

            foreach ($modelElementsByType as $element) {
                $resultCollector->setCurrentElement($element);

                try {
                    $validator->validate($element, $resultCollector);
                } catch (\Exception $e) {
                    throw new Exception("validator threw an exception while validating an element");
                }
            }
        }

        return $resultsCollector->getResults();
    }
}
