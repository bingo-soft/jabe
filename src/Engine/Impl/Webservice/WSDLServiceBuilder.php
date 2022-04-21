<?php

namespace BpmPlatform\Engine\Impl\Webservice;

use BpmPlatform\Model\Wsdl\Instance\{
    DefinitionsInterface,
    ServiceInterface
};

class WSDLServiceBuilder
{
    public function __construct()
    {
    }

    public function buildServices(DefinitionsInterface $def): array
    {
        $res = [];
        $children = $def->getRootElements();
        foreach ($children as $child) {
            if ($child instanceof ServiceInterface) {
                $res[] = $child;
            }
        }
        return $res;
    }
}
