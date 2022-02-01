<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\History\HistoricProcessInstanceInterface;
use BpmPlatform\Engine\Impl\History\Event\HistoricProcessInstanceEventEntity;

class HistoricProcessInstanceEntity extends HistoricProcessInstanceEventEntity implements HistoricProcessInstanceInterface
{
}
