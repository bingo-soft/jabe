<?php

namespace BpmPlatform\Model\Xml\Validation;

use BpmPlatform\Model\Xml\StringWriter;
use BpmPlatform\Model\Xml\Instance\ModelElementInstanceInterface;

interface ValidationResultFormatterInterface
{
    public function formatElement(StringWriter $writer, ModelElementInstanceInterface $element): void;

    public function formatResult(StringWriter $writer, ValidationResultInterface $result): void;
}
