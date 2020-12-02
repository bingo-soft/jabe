<?php

namespace BpmPlatform\Engine\Authorization;

/**
 * Holds the set of built-in user identities.
 *
 */
interface GroupInterface
{
    public const ADMIN = "ADMIN";
    public const GROUP_TYPE_SYSTEM = "SYSTEM";
    public const GROUP_TYPE_WORKFLOW = "WORKFLOW";
}
