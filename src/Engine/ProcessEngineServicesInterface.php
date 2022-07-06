<?php

namespace Jabe\Engine;

interface ProcessEngineServicesInterface
{
    /**
     * Returns the process engine's RuntimeService.
     *
     * @return RuntimeServiceInterface the RuntimeService object.
     */
    public function getRuntimeService(): RuntimeServiceInterface;

    /**
     * Returns the process engine's RepositoryService.
     *
     * @return RepositoryServiceInterface the RepositoryService object.
     */
    public function getRepositoryService(): RepositoryServiceInterface;

    /**
     * Returns the process engine's FormService.
     *
     * @return FormServiceInterface the FormService object.
     */
    public function getFormService(): FormServiceInterface;

    /**
     * Returns the process engine's TaskService.
     *
     * @return TaskServiceInterface the TaskService object.
     */
    public function getTaskService(): TaskServiceInterface;

    /**
     * Returns the process engine's HistoryService.
     *
     * @return HistoryServiceInterface the HistoryService object.
     */
    public function getHistoryService(): HistoryServiceInterface;

    /**
     * Returns the process engine's IdentityService.
     *
     * @return IdentityServiceInterface the IdentityService object.
     */
    public function getIdentityService(): IdentityServiceInterface;

    /**
     * Returns the process engine's ManagementService.
     *
     * @return ManagementServiceInterface the ManagementService object.
     */
    public function getManagementService(): ManagementServiceInterface;

    /**
     * Returns the process engine's AuthorizationService.
     *
     * @return AuthorizationServiceInterface the AuthorizationService object.
     */
    public function getAuthorizationService(): AuthorizationServiceInterface;

    /**
     * Returns the engine's FilterService.
     *
     * @return FilterServiceInterface the FilterService object.
     *
     */
    public function getFilterService(): FilterServiceInterface;

    /**
     * Returns the engine's ExternalTaskService.
     *
     * @return ExternalTaskServiceInterface the ExternalTaskService object.
     */
    public function getExternalTaskService(): ExternalTaskServiceInterface;
}
