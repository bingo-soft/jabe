<?php

namespace BpmPlatform\Engine\Runtime;

interface ProcessElementInstanceInterface
{
    /** The id of the process element instance */
    public function getId(): ?string;

    /** The id of the parent activity instance. */
    public function getParentActivityInstanceId(): ?string;

    /** the process definition id */
    public function getProcessDefinitionId(): string;

    /** the id of the process instance this process element is part of */
    public function getProcessInstanceId(): string;
}
