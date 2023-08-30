<?php

namespace Jabe\Impl\Bpmn\Webservice;

interface OperationImplementationInterface
{
    /**
     * @return string the id of this implementation
     */
    public function getId(): ?string;

    /**
     * @return string the name of this implementation
     */
    public function getName(): ?string;
}
