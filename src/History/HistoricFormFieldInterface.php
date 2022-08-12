<?php

namespace Jabe\History;

interface HistoricFormFieldInterface
{
    /** the id or key of the property */
    public function getFieldId(): string;

    /** the submitted value */
    public function getFieldValue();
}
