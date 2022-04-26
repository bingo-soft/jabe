<?php

namespace Jabe\Model\Xml\Impl\Type\Reference;

use Jabe\Model\Xml\Impl\Util\QName;
use Jabe\Model\Xml\Type\Child\ChildElementInterface;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;

class QNameElementReferenceImpl extends ElementReferenceImpl
{
    public function __construct(ChildElementInterface $referenceSourceCollection)
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
