<?php

namespace Jabe\Engine\Impl\Bpmn\Data;

interface StructureInstanceInterface
{
    /**
     * Converts this structure instance into an array
     *
     * @return this structure as an array
     */
    public function toArray(): array;

    /**
     * Loads this structure from array
     *
     * @param array the array where this structure loads data
     */
    public function loadFrom(array $array): void;
}
