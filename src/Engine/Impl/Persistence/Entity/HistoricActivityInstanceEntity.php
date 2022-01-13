<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\History\HistoricActivityInstanceInterface;
use BpmPlatform\Engine\Impl\History\Event\HistoricActivityInstanceEventEntity;

class HistoricActivityInstanceEntity extends HistoricActivityInstanceEventEntity implements HistoricActivityInstanceInterface
{
}
