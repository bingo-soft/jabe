<?php

namespace BpmPlatform\Engine\Impl\Bpmn\Parser;

use BpmPlatform\Engine\Impl\Util\Xml\Element;

interface XMLImporterInterface
{
    /**
     * Imports the definitions in the XML declared in element
     *
     * @param element the declarations to be imported
     * @param parse the parse who called this importer
     */
    public function importFrom(Element $element, BpmnParse $parse): void;
}
