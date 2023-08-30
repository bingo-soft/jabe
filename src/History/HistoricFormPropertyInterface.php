<?php

namespace Jabe\History;

interface HistoricFormPropertyInterface
{
    /** the id or key of the property */
    public function getPropertyId(): ?string;

    /** the submitted value */
    public function getPropertyValue();
}
