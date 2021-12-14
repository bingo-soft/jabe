<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\Impl\Context\Context;
use BpmPlatform\Engine\Impl\Db\{
    DbEntityInterface,
    AbstractHasDbReferences,
    HasDbRevisionInterface
};
use BpmPlatform\Engine\Impl\Event\{
    EventHandlerInterface,
    EventType
};
