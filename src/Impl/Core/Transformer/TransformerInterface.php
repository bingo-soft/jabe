<?php

namespace Jabe\Impl\Core\Transformer;

use Jabe\Impl\Core\CoreActivity;

interface TransformerInterface
{
    public function createTransform(): CoreActivity;
}
