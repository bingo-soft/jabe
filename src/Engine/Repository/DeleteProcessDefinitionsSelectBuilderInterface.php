<?php

namespace Jabe\Engine\Repository;

interface DeleteProcessDefinitionsSelectBuilderInterface
{
    /**
     * Selects process definitions with given process definition ids.
     *
     * @param processDefinitionId at least one process definition id
     * @return DeleteProcessDefinitionsBuilderInterface the builder
     */
    public function byIds(array $processDefinitionId): DeleteProcessDefinitionsBuilderInterface;

    /**
     * Selects process definitions with a given key.
     *
     * @param processDefinitionKey process definition key
     * @return DeleteProcessDefinitionsTenantBuilderInterface the builder
     */
    public function byKey(string $processDefinitionKey): DeleteProcessDefinitionsTenantBuilderInterface;
}
