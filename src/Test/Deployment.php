<?php

namespace Jabe\Test;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Deployment
{
    public function __construct(
        private mixed $resources = []
    ) {
    }

    public function resources()
    {
        return is_array($this->resources) ? $this->resources : [ $this->resources ];
    }
}
