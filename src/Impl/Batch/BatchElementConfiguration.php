<?php

namespace Jabe\Impl\Batch;

class BatchElementConfiguration
{
    protected $collectedMappings = [];

    protected $ids = [];
    protected $mappings;

    /**
     * Add mappings of deployment ids to resource ids to the overall element
     * mapping list. All elements from <code>idList</code> that are not part of
     * the mappings will be added to the list of <code>null</code> deployment id
     * mappings.
     *
     * @param mappingsList
     *          the mappings to add
     * @param idList
     *          the list of ids to check for missing elements concerning the
     *          mappings to add
     */
    public function addDeploymentMappings(array $mappingsList, array $idList): void
    {
        if (!empty($this->ids)) {
            $this->ids = [];
            $this->mappings = null;
        }
        $missingIds = empty($idList) ? [] : $idList;
        foreach ($mappingsList as $pair) {
            $deploymentId = $pair[0];
            if (!array_key_exists($deploymentId, $this->collectedMappings)) {
                $this->collectedMappings[$deploymentId] = [];
            }
            $this->collectedMappings[$deploymentId][] = $pair[1];
            if (!empty($missingIds)) {
                if (($key = array_search($pair[1], $missingIds)) !== false) {
                    unset($missingIds[$key]);
                }
            }
        }
        if (!empty($missingIds)) {
            if (!array_key_exists(0, $this->collectedMappings)) {
                $this->collectedMappings[0] = [];
            }
            $this->collectedMappings[0] = array_merge($this->collectedMappings[0], $missingIds);
        }
    }

    /**
     * @return array the list of ids that are mapped to deployment ids, ordered by
     *         deployment id
     */
    public function getIds(): array
    {
        if (empty($this->ids)) {
            $this->createDeploymentMappings();
        }
        return $this->ids;
    }

    /**
     * @return DeploymentMappings the list of DeploymentMappings
     */
    public function getMappings(): DeploymentMappings
    {
        if ($this->mappings == null) {
            $this->createDeploymentMappings();
        }
        return $this->mappings;
    }

    public function isEmpty(): bool
    {
        return empty($this->collectedMappings);
    }

    protected function createDeploymentMappings(): void
    {
        $this->ids = [];
        $this->mappings = new DeploymentMappings();

        foreach ($this->collectedMappings as $key => $mappingIds) {
            $this->ids = array_unique(array_merge($this->ids, $mappingIds));
            $this->mappings->add(new DeploymentMapping($key, count($mappingIds)));
        }
    }
}
