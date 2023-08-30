<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\History\HistoricJobLogInterface;
use Jabe\Impl\History\Event\HistoricJobLogEvent;

class HistoricJobLogEventEntity extends HistoricJobLogEvent implements HistoricJobLogInterface
{
}
