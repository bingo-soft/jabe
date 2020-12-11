<?php

namespace BpmPlatform\Model\Xml\Impl\Util\DomUtil;

use BpmPlatform\Model\Xml\Impl\ModelInstanceImpl;
use BpmPlatform\Model\Xml\Impl\Instance\DomElementImpl;
use BpmPlatform\Model\Xml\Impl\Util\ModelUtil;

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

    public function matches(\DomElement $element): bool
    {
        if (!parent::matches($element)) {
            return false;
        }
        $modelElement = ModelUtil::getModelElement(new DomElementImpl($element), $this->model);
        return $modelElement instanceof $this->type;
    }
}
