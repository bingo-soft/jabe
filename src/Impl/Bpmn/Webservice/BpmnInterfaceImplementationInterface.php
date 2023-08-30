<?php

namespace Jabe\Impl\Bpmn\Webservice;

interface BpmnInterfaceImplementationInterface
{
    /**
     * @return string the name of this implementation
     */
    public function getName(): ?string;
}
