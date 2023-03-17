<?php

namespace Tests\Api\Variables;

class FailingPhpSerializable extends PhpSerializable
{
    public function unserialize($data)
    {
        throw new \Exception("Exception while deserializing object.");
    }
}
