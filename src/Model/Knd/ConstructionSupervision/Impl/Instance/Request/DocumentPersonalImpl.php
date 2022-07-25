<?php

namespace Jabe\Model\Knd\ConstructionSupervision\Impl\Instance\Request;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Knd\ConstructionSupervision\Impl\RequestModelConstants;
use Jabe\Model\Knd\ConstructionSupervision\Instance\Request\{
    DocnumberInterface,
    DocumentPersonalInterface,
    DocseriesInterface,
    IssuedateInterface,
    IssueidPassportRFInterface,
    IssueorgInterface,
    NameDocInterface,
    TypeDocInterface
};

class DocumentPersonalImpl extends ModelElementInstanceImpl implements DocumentPersonalInterface
{
    private $docnumber;
    private $docseries;
    private $issuedate;
    private $issueidPassportRF;
    private $issueorg;
    private $nameDoc;
    private $typeDoc;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            DocumentPersonalInterface::class,
            RequestModelConstants::ELEMENT_NAME_DOCUMENT_PERSONAL
        )
        ->namespaceUri(RequestModelConstants::MODEL_NAMESPACE)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): DocumentPersonalInterface
                {
                    return new DocumentPersonalImpl($instanceContext);
                }
            }
        );

        self::$docnumber = $sequenceBuilder->element(DocnumberInterface::class)
        ->build();
        self::$docseries = $sequenceBuilder->element(DocseriesInterface::class)
        ->build();
        self::$issuedate = $sequenceBuilder->element(IssuedateInterface::class)
        ->build();
        self::$issueidPassportRF = $sequenceBuilder->element(IssueidPassportRFInterface::class)
        ->build();
        self::$issueorg = $sequenceBuilder->element(IssueorgInterface::class)
        ->build();
        self::$nameDoc = $sequenceBuilder->element(NameDocInterface::class)
        ->build();
        self::$typeDoc = $sequenceBuilder->element(TypeDocInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public function getDocnumber(): DocnumberInterface
    {
        return self::$docnumber->getChild($this);
    }

    public function getDocseries(): DocseriesInterface
    {
        return self::$docseries->getChild($this);
    }

    public function getIssuedate(): IssuedateInterface
    {
        return self::$issuedate->getChild($this);
    }

    public function getIssueidPassportRF(): IssueidPassportRFInterface
    {
        return self::$issueidPassportRF->getChild($this);
    }

    public function getIssueorg(): IssueorgInterface
    {
        return self::$issueorg->getChild($this);
    }

    public function getNameDoc(): NameDocInterface
    {
        return self::$nameDoc->getChild($this);
    }

    public function getTypeDoc(): TypeDocInterface
    {
        return self::$typeDoc->getChild($this);
    }
}
