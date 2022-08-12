<?php

namespace Jabe\Authorization;

/**
 * Resources are entities for which a user or a group is authorized. Examples of
 * resources are applications, process-definitions, process-instances, tasks ...
 *
 * A resource has a type and an id. The type allows to group all resources of the
 * same kind. A resource id is the identifier of an individual resource instance
 * For example: the resource type could be "processDefinition"
 * and the resource-id could be the id of an individual process definition. *
 */
interface ResourceInterface
{
    public function resourceName(): string;

    public function resourceType(): int;
}
