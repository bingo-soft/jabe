<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Parser;

use BpmPlatform\Engine\Impl\Util\Xml\Element;

interface XMLImporterInterface
{
    /**
     * Imports the definitions in the XML declared in element or path
     *
     * @param Element|string element the declarations to be imported or path to file
     */
    public function importFrom($element): void;
}
