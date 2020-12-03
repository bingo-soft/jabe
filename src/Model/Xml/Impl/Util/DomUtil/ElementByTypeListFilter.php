<?php

namespace BpmPlatform\Model\Xml\Impl\Util\DomUtil;

use BpmPlatform\Model\Xml\Impl\ModelInstanceImpl;
use BpmPlatform\Model\Xml\Impl\Util\ModelUtil\ModelUtils;

class ElementByTypeListFilter extends ElementNodeListFilter
{
    private $type;
    private $model;

    /**
     * @param mixed $type
     */
    public function __construct($type, ModelInstanceImpl $modelInstance)
    {
        $this->type = $type;
        $this->model = $modelInstance;
    }

    public function matches(\DomNode $node): bool
    {
        if (!parent::matches($node)) {
            return false;
        }

        //@TODO
    }
}
