<?php

namespace Jabe\Test;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RequiredHistoryLevel
{
    public function __construct(
        private ?string $value
    ) {
    }

    public function value(): ?string
    {
        return $this->value;
    }
}
