<?php

namespace BpmPlatform\Engine\Impl\Core\Transformer;

use BpmPlatform\Engine\Impl\Core\CoreActivity;

interface TransformerInterface
{
    public function createTransform(): CoreActivity;
}
