<?php

namespace Jabe\Engine\Impl\Bpmn\Parser;

use Jabe\Engine\Impl\Util\Xml\Element;

interface XMLImporterInterface
{
    /**
     * Imports the definitions in the XML declared in element or path
     *
     * @param Element|string element the declarations to be imported or path to file
     */
    public function importFrom($element): void;
}
