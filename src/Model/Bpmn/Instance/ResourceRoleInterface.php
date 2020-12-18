<?php

namespace BpmPlatform\Model\Bpmn\Instance;

interface ResourceRoleInterface extends BaseElementInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getResource(): ResourceInterface;

    public function setResource(ResourceInterface $resource): void;

    public function getResourceParameterBinding(): array;

    public function getResourceAssignmentExpression(): ResourceAssignmentExpressionInterface;
}
