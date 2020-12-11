<?php

namespace BpmPlatform\Model\Xml\Impl\Validation;

use BpmPlatform\Model\Xml\Validation\{
    ValidationResultCollectorInterface,
    ValidationResultType,
    ValidationResultsInterface
};
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;

class ValidationResultsCollectorImpl implements ValidationResultCollector
{
    protected $currentElement;
    protected $collectedResults = [];
    protected $errorCount = 0;
    protected $warningCount = 0;

    public function addError(int $code, string $message): void
    {
        $resultsByElement = $this->resultsForCurrentElement();
        $resultsByElement['value'][] = new ModelValidationResultImpl(
            $this->currentElement,
            ValidationResultType::ERROR,
            $code,
            $message
        );
        foreach ($this->collectedResults as $key => $collectedResult) {
            if ($collectedResult['key'] == $this->currentElement) {
                $this->collectedResults[$key] = [
                    'key' => $this->currentElement,
                    'value' => $resultsByElement['value']
                ];
                break;
            }
        }
    }

    public function addWarning(int $code, string $message): void
    {
        $resultsByElement = $this->resultsForCurrentElement();
        $resultsByElement['value'][] = new ModelValidationResultImpl(
            $this->currentElement,
            ValidationResultType::WARNING,
            $code,
            $message
        );
        foreach ($this->collectedResults as $key => $collectedResult) {
            if ($collectedResult['key'] == $this->currentElement) {
                $this->collectedResults[$key] = [
                    'key' => $this->currentElement,
                    'value' => $resultsByElement['value']
                ];
                break;
            }
        }
    }

    protected function resultsForCurrentElement(): array
    {
        $exists = false;
        foreach ($this->collectedResults as $collectedResult) {
            if ($collectedResult['key'] == $this->currentElement) {
                $resultsByElement = $collectedResult['value'];
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $res = [
                'key' => $this->currentElement,
                'value' => []
            ];
            $this->collectedResults[] = $res;
        } else {
            $res = [
                'key' => $this->currentElement,
                'value' => $resultsByElement
            ];
            $this->collectedResults[] = $res;
        }
        return $res;
    }

    public function setCurrentElement(ModelElementInstanceInterface $currentElement): void
    {
        $this->currentElement = $currentElement;
    }

    public function getResults(): ValidationResultsInterface
    {
        return new ModelValidationResultsImpl($this->collectedResults, $this->errorCount, $this->warningCount);
    }
}
