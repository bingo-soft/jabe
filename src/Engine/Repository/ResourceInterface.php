<?php

namespace BpmPlatform\Engine\Repository;

interface ResourceInterface
{
    public function getId(): string;

    public function getName(): string;

    public function getDeploymentId(): ?string;
}
