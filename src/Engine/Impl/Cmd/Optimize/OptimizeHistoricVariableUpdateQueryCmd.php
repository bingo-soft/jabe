<?php

namespace Jabe\Engine\Impl\Cmd\Optimize;

use Jabe\Engine\History\HistoricVariableUpdateInterface;
use Jabe\Engine\Impl\ProcessEngineLogger;
use Jabe\Engine\Impl\Cmd\CommandLogger;
use Jabe\Engine\Impl\Interceptor\{
    CommandInterface,
    CommandContext
};
use Jabe\Engine\Impl\Persistence\Entity\HistoricDetailVariableInstanceUpdateEntity;
use Jabe\Engine\Impl\Util\CollectionUtil;
use Jabe\Engine\Impl\Variable\Serializer\AbstractTypedValueSerializer;
use Jabe\Engine\Variable\Type\ValueType;

class OptimizeHistoricVariableUpdateQueryCmd implements CommandInterface
{
    protected $occurreAfter;
    protected $occurreAt;
    protected $excludeObjectValues;
    protected $maxResults;

    public function __construct(string $occurreAfter, string $occurreAt, bool $excludeObjectValues, int $maxResults)
    {
        $this->occurreAfter = $occurreAfter;
        $this->occurreAt = $occurreAt;
        $this->excludeObjectValues = $excludeObjectValues;
        $this->maxResults = $maxResults;
    }

    public function execute(CommandContext $commandContext)
    {
        $historicVariableUpdates = $commandContext->getOptimizeManager()->getHistoricVariableUpdates($this->occurreAfter, $this->occurreAt, $this->maxResults);

        $this->fetchVariableValues($historicVariableUpdates, $commandContext);
        return $historicVariableUpdates;
    }

    private function fetchVariableValues(array &$historicVariableUpdates, CommandContext $commandContext): void
    {
        if (!CollectionUtil::isEmpty($historicVariableUpdates)) {
            $byteArrayIds = $this->getByteArrayIds($historicVariableUpdates);
            if (!empty($byteArrayIds)) {
                // pre-fetch all byte arrays into dbEntityCache to avoid (n+1) number of queries
                $commandContext->getOptimizeManager()->fetchHistoricVariableUpdateByteArrays($byteArrayIds);
            }

            $this->resolveTypedValues($historicVariableUpdates);
        }
    }

    protected function shouldFetchValue(HistoricDetailVariableInstanceUpdateEntity $entity): bool
    {
        $entityType = $entity->getSerializer()->getType();
        // do no fetch values for byte arrays/blob variables (e.g. files or bytes)
        return !in_array($entityType->getName(), AbstractTypedValueSerializer::BINARY_VALUE_TYPES)
        // nor object values unless enabled
        && (ValueType::getObject() != $entityType || !$this->excludeObjectValues);
    }

    protected function isHistoricDetailVariableInstanceUpdateEntity(HistoricVariableUpdateInterface $variableUpdate): bool
    {
        return $variableUpdate instanceof HistoricDetailVariableInstanceUpdateEntity;
    }

    protected function getByteArrayIds(array $variableUpdates): array
    {
        $byteArrayIds = [];

        foreach ($variableUpdates as $variableUpdate) {
            if ($this->isHistoricDetailVariableInstanceUpdateEntity($variableUpdate)) {
                $entity = $variableUpdate;

                if ($this->shouldFetchValue($entity)) {
                    $byteArrayId = $entity->getByteArrayValueId();
                    if ($byteArrayId !== null) {
                        $byteArrayIds[] = $byteArrayId;
                    }
                }
            }
        }

        return $byteArrayIds;
    }

    protected function resolveTypedValues(array &$variableUpdates): void
    {
        foreach ($variableUpdates as $variableUpdate) {
            if ($this->isHistoricDetailVariableInstanceUpdateEntity($variableUpdate)) {
                $entity = $variableUpdate;
                if ($this->shouldFetchValue($entity)) {
                    try {
                        $entity->getTypedValue(false);
                    } catch (\Exception $t) {
                        // do not fail if one of the variables fails to load
                        //LOG.exceptionWhileGettingValueForVariable(t);
                    }
                }
            }
        }
    }
}
