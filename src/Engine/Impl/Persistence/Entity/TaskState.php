<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

class TaskState
{
    public const STATE_INIT = 0;
    public const STATE_CREATED = 1;
    public const STATE_COMPLETED = 2;
    public const STATE_DELETED = 3;
}
