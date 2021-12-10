<?php

namespace BpmPlatform\Engine\Impl\Persistence\Entity;

use BpmPlatform\Engine\Authorization\{
    AuthorizationInterface,
    GroupsInterface,
    Permissions,
    ProcessDefinitionPermissions,
    Resources,
    TaskPermissions
};
use BpmPlatform\Engine\{
    AuthorizationException,
    ProcessEngineConfiguration
};
