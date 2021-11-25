<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Parser;

use BpmPlatform\Engine\{
    ProblemInterface,
    ResourceReportInterface
};

class ResourceReportImpl implements ResourceReportInterface
{
    protected $resourceName;
    protected $errors = [];
    protected $warnings = [];

    public function __construct(string $resourceName, array $errors, array $warnings)
    {
        $this->resourceName = $resourceName;
        $this->errors = $errors;
        $this->warnings = $warnings;
    }

    public function getResourceName(): string
    {
        return $this->resourceName;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }
}
