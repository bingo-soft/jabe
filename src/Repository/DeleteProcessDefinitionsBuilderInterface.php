<?php

namespace Jabe\Repository;

interface DeleteProcessDefinitionsBuilderInterface
{
    /**
     * All process instances of the process definition as well as history data is deleted.
     *
     * @return DeleteProcessDefinitionsBuilderInterface the builder
     */
    public function cascade(): DeleteProcessDefinitionsBuilderInterface;

    /**
     * Only the built-in ExecutionListeners are notified with the
     * ExecutionListener#EVENTNAME_END event.
     * Is only applied in conjunction with the cascade method.
     *
     * @return DeleteProcessDefinitionsBuilderInterface the builder
     */
    public function skipCustomListeners(): DeleteProcessDefinitionsBuilderInterface;

    /**
     * Specifies whether input/output mappings for tasks should be invoked
     *
     * @return DeleteProcessDefinitionsBuilderInterface the builder
     */
    public function skipIoMappings(): DeleteProcessDefinitionsBuilderInterface;

    /**
     * Performs the deletion of process definitions.
     *
     * @throws ProcessEngineException
     *           If no such processDefinition can be found.
     * @throws AuthorizationException
     */
    public function delete(): void;
}
