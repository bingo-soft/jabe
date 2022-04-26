<?php

namespace Jabe\Model\Xml\Impl\Type\Reference;

use Jabe\Model\Xml\Impl\Util\QName;
use Jabe\Model\Xml\Type\Child\ChildElementCollectionInterface;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;

class QNameElementReferenceCollectionImpl extends ElementReferenceCollectionImpl
{
    public function __construct(ChildElementCollectionInterface $referenceSourceCollection)
    {
        parent::__construct($referenceSourceCollection);
    }

    /**
     * @return mixed
     */
    public function getReferenceIdentifier(ModelElementInstanceInterface $referenceSourceElement)
    {
        $identifier = parent::getReferenceIdentifier($referenceSourceElement);
        if (!empty($identifier)) {
            $qName = QName::parseQName($identifier);
            return $qName->getLocalName();
        } else {
            return null;
        }
    }
}
