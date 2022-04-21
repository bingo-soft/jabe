<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Webservice;

interface OperationImplementationInterface
{
    /**
     * @return the id of this implementation
     */
    public function getId(): string;

    /**
     * @return the name of this implementation
     */
    public function getName(): string;
}
