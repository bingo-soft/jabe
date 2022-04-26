<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\History\HistoricTaskInstanceInterface;
use Jabe\Engine\Impl\History\Event\HistoricTaskInstanceEventEntity;

class HistoricTaskInstanceEntity extends HistoricTaskInstanceEventEntity implements HistoricTaskInstanceInterface
{
}
