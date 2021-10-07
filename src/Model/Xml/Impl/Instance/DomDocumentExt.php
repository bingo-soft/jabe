<?php

namespace BpmPlatform\Model\Xml\Impl\Instance;

class DomDocumentExt extends \DOMDocument
{
    public function __construct()
    {
        parent::__construct();
        $this->registerNodeClasses();
    }

    private function registerNodeClasses()
    {
        $this->registerNodeClass('DOMElement', DomElementExt::class);
    }
}
