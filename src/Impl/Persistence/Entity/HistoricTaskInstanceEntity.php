<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\History\HistoricTaskInstanceInterface;
use Jabe\Impl\History\Event\HistoricTaskInstanceEventEntity;

class HistoricTaskInstanceEntity extends HistoricTaskInstanceEventEntity implements HistoricTaskInstanceInterface
{
}
