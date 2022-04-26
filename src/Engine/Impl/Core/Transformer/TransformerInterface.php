<?php

namespace Jabe\Engine\Impl\Core\Transformer;

use Jabe\Engine\Impl\Core\CoreActivity;

interface TransformerInterface
{
    public function createTransform(): CoreActivity;
}
