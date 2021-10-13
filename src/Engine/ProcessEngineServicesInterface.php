<?php

namespace BpmPlatform\Engine;

interface ProcessEngineServicesInterface
{
    /**
     * Returns the process engine's RuntimeService.
     *
     * @return the RuntimeService object.
     */
    public function getRuntimeService(): RuntimeServiceInterface;

    /**
     * Returns the process engine's RepositoryService.
     *
     * @return the RepositoryService object.
     */
    public function getRepositoryService(): RepositoryServiceInterface;

    /**
     * Returns the process engine's FormService.
     *
     * @return the FormService object.
     */
    public function getFormService(): FormServiceInterface;

    /**
     * Returns the process engine's TaskService.
     *
     * @return the TaskService object.
     */
    public function getTaskService(): TaskServiceInterface;

    /**
     * Returns the process engine's HistoryService.
     *
     * @return the HistoryService object.
     */
    public function getHistoryService(): HistoryServiceInterface;

    /**
     * Returns the process engine's IdentityService.
     *
     * @return the IdentityService object.
     */
    public function getIdentityService(): IdentityServiceInterface;

    /**
     * Returns the process engine's ManagementService.
     *
     * @return the ManagementService object.
     */
    public function getManagementService(): ManagementServiceInterface;

    /**
     * Returns the process engine's AuthorizationService.
     *
     * @return the AuthorizationService object.
     */
    public function getAuthorizationService(): AuthorizationServiceInterface;

    /**
     * Returns the engine's FilterService.
     *
     * @return the FilterService object.
     *
     */
    public function getFilterService(): FilterServiceInterface;

    /**
     * Returns the engine's ExternalTaskService.
     *
     * @return the ExternalTaskService object.
     */
    public function getExternalTaskService(): ExternalTaskServiceInterface;
}
