<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\History\HistoricProcessInstanceInterface;
use Jabe\Impl\History\Event\HistoricProcessInstanceEventEntity;

class HistoricProcessInstanceEntity extends HistoricProcessInstanceEventEntity implements HistoricProcessInstanceInterface
{
}
