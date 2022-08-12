<?php

namespace Jabe\History;

use Jabe\Query\NativeQueryInterface;

interface NativeHistoricVariableInstanceQueryInterface extends NativeQueryInterface
{
    /**
     * Disable deserialization of variable values that are custom objects. By default, the query
     * will attempt to deserialize the value of these variables. By calling this method you can
     * prevent such attempts in environments where their classes are not available.
     * Independent of this setting, variable serialized values are accessible.
     */
    public function disableCustomObjectDeserialization(): NativeHistoricVariableInstanceQueryInterface;
}
