<?php

namespace Tests\Api\Variables;

class FailingPhpSerializable extends PhpSerializable
{
    public function __unserialize(array $data): void
    {
        throw new \Exception("Exception while deserializing object.");
    }
}
