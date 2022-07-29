<?php

namespace Jabe\Engine\Task;

interface IdentityLinkInterface
{
    /**
     * Get the Id of identityLink
     */
    public function getId(): string;
    /**
     * Returns the type of link.
     * See IdentityLinkType for the native supported types by the process engine.
     */
    public function getType(): string;

    /**
     * If the identity link involves a user, then this will be a non-null id of a user.
     * That userId can be used to query for user information through the UserQuery API.
     */
    public function getUserId(): string;

    /**
     * If the identity link involves a group, then this will be a non-null id of a group.
     * That groupId can be used to query for user information through the GroupQuery API.
     */
    public function getGroupId(): string;

    /**
     * The id of the task associated with this identity link.
     */
    public function getTaskId(): string;

    /**
     * Get the process definition id
     */
    public function getProcessDefId(): string;

    /**
     * The id of the tenant associated with this identity link
     */
    public function getTenantId(): ?string;
}
