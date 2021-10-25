<?php

namespace BpmPlatform\Engine;

interface ResourceReportInterface
{
    /** Returns the resource name where the problems occurred. */
    public function getResourceName(): string;

    /** Returns list of errors in this report */
    public function getErrors(): array;

    /** Returns list of warnings in this report */
    public function getWarnings(): array;
}
