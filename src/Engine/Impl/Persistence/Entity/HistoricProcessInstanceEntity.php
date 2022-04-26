<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\History\HistoricProcessInstanceInterface;
use Jabe\Engine\Impl\History\Event\HistoricProcessInstanceEventEntity;

class HistoricProcessInstanceEntity extends HistoricProcessInstanceEventEntity implements HistoricProcessInstanceInterface
{
}
