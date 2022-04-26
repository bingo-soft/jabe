<?php

namespace Tests\Knd;

use PHPUnit\Framework\TestCase;
use Jabe\Model\Xml\ModelInstanceInterface;
use Jabe\Model\Xml\Impl\Parser\AbstractModelParser;
use Jabe\Model\Knd\Complaints\Instance\Response\{
    CodeInterface,
    InspectionResultInterface,
    KndResponseInterface,
    StatusInterface
};
use Jabe\Model\Knd\Complaints\Impl\{
    KndResponseParser,
    KndResponseModelConstants
};

class KndResponseCreateTest extends TestCase
{
    protected $modelParser;

    protected $modelInstance;

    protected function setUp(): void
    {
        $this->modelParser = new KndResponseParser();
        $this->modelInstance = $this->modelParser->getEmptyModel();

        $response = $this->modelInstance->newInstance(KndResponseInterface::class);
        $this->modelInstance->setDocumentElement($response);

        $response->getDomElement()->registerNamespace("tns", KndResponseModelConstants::MODEL_NAMESPACE);
    }

    public function testModel(): void
    {
        $response = $this->modelInstance->getDocumentElement();
        $inspectionResult = $this->modelInstance->newInstance(InspectionResultInterface::class);

        $status = $this->modelInstance->newInstance(StatusInterface::class);
        $code = $this->modelInstance->newInstance(CodeInterface::class);
        $code->setTextContent("911");

        $status->setCode($code);

        $inspectionResult->setStatus($status);

        $response->setInspectionResult($inspectionResult);

        $source = $this->modelInstance->getDocument()->getDomSource();

        $this->assertTrue(true);
    }
}
