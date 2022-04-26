<?php

namespace Jabe\Model\Xml\Impl\Type\Reference;

use Jabe\Model\Xml\Impl\Type\Attribute\AttributeImpl;
use Jabe\Model\Xml\Impl\Util\QName;
use Jabe\Model\Xml\Instance\ModelElementInstanceInterface;

class QNameAttributeReferenceImpl extends AttributeReferenceImpl
{
    public function __construct(AttributeImpl $referenceSourceAttribute)
    {
        parent::__construct($referenceSourceAttribute);
    }

    public function getReferenceIdentifier(ModelElementInstanceInterface $referenceSourceElement): ?string
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
