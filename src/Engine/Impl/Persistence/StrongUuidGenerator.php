<?php

namespace BpmPlatform\Engine\Impl\Persistence;

use Ramsey\Uuid\Uuid;
use BpmPlatform\Engine\Impl\Cfg\IdGenerator;

class StrongUuidGenerator implements IdGeneratorInterface
{
    // different ProcessEngines on the same classloader share one generator.
    public function __construct()
    {
    }

    public function getNextId(): string
    {
        return Uuid::uuid1();
    }
}
