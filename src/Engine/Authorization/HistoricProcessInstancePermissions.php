<?php

namespace BpmPlatform\Engine\Authorization;

use BpmPlatform\Engine\Authorization\Exception\PermissionNotFound;

/**
 * The set of built-in Permissions for Resources::HISTORIC_PROCESS_INSTANCE.
 */
class HistoricProcessInstancePermissions extends AbstractPermissions
{
    public const NONE = 0;
    public const ALL = PHP_INT_MAX;
    public const READ = 2;

    private const RESOURCES = [ Resources::HISTORIC_PROCESS_INSTANCE ];
}
