<?php

namespace Jabe\Model\Bpmn\Instance;

use Jabe\Model\Bpmn\Impl\Instance\DataPath;

interface CorrelationPropertyBindingInterface extends BaseElementInterface
{
    public function getCorrelationProperty(): CorrelationPropertyInterface;

    public function setCorrelationProperty(CorrelationPropertyInterface $correlationProperty): void;

    public function getDataPath(): DataPath;

    public function setDataPath(DataPath $dataPath): void;
}
