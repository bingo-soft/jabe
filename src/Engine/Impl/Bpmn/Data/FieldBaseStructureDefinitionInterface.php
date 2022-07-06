<?php

namespace Jabe\Engine\Impl\Bpmn\Data;

interface FieldBaseStructureDefinitionInterface extends StructureDefinitionInterface
{
    /**
     * Obtains the number of fields that this structure has
     *
     * @return int the number of fields that this structure has
     */
    public function getFieldSize(): int;

    /**
     * Obtains the name of the field in the index position
     *
     * @param index
     *            the position of the field
     * @return string the name of the field
     */
    public function getFieldNameAt(int $index): ?string;

    /**
     * Obtains the type of the field in the index position
     *
     * @param index
     *            the position of the field
     * @return string the type of the field
     */
    public function getFieldTypeAt(int $index): ?string;

    /**
     * Obtains the parameter type of the field in the index position
     *
     * @param index
     *            the position of the field
     * @return string the parameter type of the field, or {@code null} if the type is not a parameterized type
     */
    public function getFieldParameterTypeAt(int $index): ?string;
}
