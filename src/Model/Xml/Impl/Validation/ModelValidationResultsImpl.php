<?php

namespace Jabe\Model\Xml\Impl\Validation;

use Jabe\Model\Xml\StringWriter;
use Jabe\Model\Xml\Validation\{
    ValidationResultsInterface,
    ValidationResultFormatterInterface
};

class ModelValidationResultsImpl implements ValidationResultsInterface
{
    protected $collectedResults;
    protected $errorCount;
    protected $warningCount;

    public function __construct(array $collectedResults, int $errorCount, int $warningCount)
    {
        $this->collectedResults = $collectedResults;
        $this->errorCount = $errorCount;
        $this->warningCount = $warningCount;
    }

    public function hasErrors(): bool
    {
        return $this->errorCount > 0;
    }

    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    public function getWarinigCount(): int
    {
        return $this->warningCount;
    }

    public function write(StringWriter $writer, ValidationResultFormatterInterface $formatter): void
    {
        foreach ($this->collectedResults as $entry) {
            $element = $entry['key'];
            $results = $entry['value'];

            $formatter->formatElement($writer, $element);

            foreach ($results as $result) {
                $formatter->formatResult($writer, $result);
            }
        }
    }

    public function getResults(): array
    {
        return $this->collectedResults;
    }
}
