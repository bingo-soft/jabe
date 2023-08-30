<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\History\{
    HistoricFormFieldInterface,
    HistoricFormPropertyInterface
};
use Jabe\Impl\History\Event\HistoricFormPropertyEventEntity;

class HistoricFormPropertyEntity extends HistoricFormPropertyEventEntity implements HistoricFormPropertyInterface, HistoricFormFieldInterface
{
    public function getPropertyValue()
    {
        if ($this->propertyValue !== null) {
            return strval($this->propertyValue);
        } else {
            return null;
        }
    }

    public function getFieldId(): ?string
    {
        return $this->propertyId;
    }

    public function getFieldValue()
    {
        return $this->propertyValue;
    }
}
