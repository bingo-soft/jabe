<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\History\HistoricJobLogInterface;
use Jabe\Engine\Impl\History\Event\HistoricJobLogEvent;

class HistoricJobLogEventEntity extends HistoricJobLogEvent implements HistoricJobLogInterface
{
}
