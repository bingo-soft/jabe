<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\History\{
    HistoricFormFieldInterface,
    HistoricFormPropertyInterface
};
use BpmPlatform\Engine\Impl\History\Event\HistoricFormPropertyEventEntity;

class HistoricFormPropertyEntity extends HistoricFormPropertyEventEntity implements HistoricFormPropertyInterface, HistoricFormFieldInterface
{
    public function getPropertyValue()
    {
        if ($this->propertyValue != null) {
            return strval($this->propertyValue);
        } else {
            return null;
        }
    }

    public function getFieldId(): string
    {
        return $this->propertyId;
    }

    public function getFieldValue()
    {
        return $this->propertyValue;
    }
}
