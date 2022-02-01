<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\History\HistoricTaskInstanceInterface;
use BpmPlatform\Engine\Impl\History\Event\HistoricTaskInstanceEventEntity;

class HistoricTaskInstanceEntity extends HistoricTaskInstanceEventEntity implements HistoricTaskInstanceInterface
{
}
