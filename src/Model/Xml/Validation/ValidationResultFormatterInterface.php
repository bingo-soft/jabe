<?php

namespace Jabe\Model\Xml\Validation;

use Jabe\Model\Xml\StringWriter;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;

interface ValidationResultFormatterInterface
{
    public function formatElement(StringWriter $writer, ModelElementInstanceInterface $element): void;

    public function formatResult(StringWriter $writer, ValidationResultInterface $result): void;
}
