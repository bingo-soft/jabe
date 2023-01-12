<?php

namespace Jabe\Impl\Test;

use Attribute;

#[Attribute(Attribute::TARGET_ALL)]
class RequiredDatabase
{
    public function excludes()
    {
        return [];
    }

    public function includes()
    {
        return [];
    }
}
