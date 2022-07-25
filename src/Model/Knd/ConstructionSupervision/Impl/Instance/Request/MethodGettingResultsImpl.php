<?php

namespace Jabe\Model\Knd\ConstructionSupervision\Impl\Instance\Request;

use Jabe\Model\Xml\ModelBuilder;
use Jabe\Model\Xml\Impl\Instance\ModelElementInstanceImpl;
use Jabe\Model\Xml\Impl\Instance\ModelTypeInstanceContext;
use Jabe\Model\Xml\Type\ModelTypeInstanceProviderInterface;
use Jabe\Model\Knd\ConstructionSupervision\Impl\RequestModelConstants;
use Jabe\Model\Knd\ConstructionSupervision\Instance\Request\{
    IsPaperDocumentRequiredInterface,
    MethodGettingResultsInterface
};

class MethodGettingResultsImpl extends ModelElementInstanceImpl implements MethodGettingResultsInterface
{
    private $isPaperDocumentRequired;

    public static function registerType(ModelBuilder $modelBuilder): void
    {
        $typeBuilder = $modelBuilder->defineType(
            MethodGettingResultsInterface::class,
            RequestModelConstants::ELEMENT_NAME_METHOD_GETTING_RESULTS
        )
        ->namespaceUri(RequestModelConstants::MODEL_NAMESPACE)
        ->instanceProvider(
            new class implements ModelTypeInstanceProviderInterface
            {
                public function newInstance(ModelTypeInstanceContext $instanceContext): MethodGettingResultsInterface
                {
                    return new MethodGettingResultsImpl($instanceContext);
                }
            }
        );

        self::$isPaperDocumentRequired = $sequenceBuilder->element(IsPaperDocumentRequiredInterface::class)
        ->build();

        $typeBuilder->build();
    }

    public function __construct(ModelTypeInstanceContext $instanceContext)
    {
        parent::__construct($instanceContext);
    }

    public function getIsPaperDocumentRequired(): IsPaperDocumentRequiredInterface
    {
        return self::$ssPaperDocumentRequired->getChild($this);
    }
}
