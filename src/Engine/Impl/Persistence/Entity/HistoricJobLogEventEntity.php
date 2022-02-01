<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\History\HistoricJobLogInterface;
use BpmPlatform\Engine\Impl\History\Event\HistoricJobLogEvent;

class HistoricJobLogEventEntity extends HistoricJobLogEvent implements HistoricJobLogInterface
{
}
