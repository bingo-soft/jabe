<?php

namespace BpmPlatform\Model\Xml\Impl\Parser;

use BpmPlatform\Model\Xml\ModelInstanceInterface;
use BpmPlatform\Model\Xml\Exception\ModelValidationException;
use BpmPlatform\Model\Xml\Impl\Util\{
    DomUtil,
    ReflectUtil
};
use BpmPlatform\Model\Xml\Instance\DomDocumentInterface;

abstract class AbstractModelParser
{
    protected $schemas = [];

    public function parseModelFromStream(string $inputStream): ModelInstanceInterface
    {
        $document = DomUtil::parseInputStream($inputStream);
        $this->validateModel($document);
        return $this->createModelInstance($document);
    }

    public function getEmptyModel(): ModelInstanceInterface
    {
        $document = DomUtil::getEmptyDocument();
        return $this->createModelInstance($document);
    }

    public function validateModel(DomDocumentInterface $document): void
    {
        $schema = $this->getSchema($document);
        if (empty($schema)) {
            return;
        }
        $dom = $document->getDomSource();
        try {
            $dom->schemaValidateSource($schema);
        } catch (\Exception $e) {
            throw new ModelValidationException("Error during DOM document validation");
        }
    }

    protected function getSchema(DomDocumentInterface $document): string
    {
        $rootElement = $document->getRootElement();
        $namespaceURI = $rootElement->getNamespaceURI();
        return $this->schemas[$namespaceURI];
    }

    protected function addSchema(string $namespaceURI, string $schema): void
    {
        $this->schemas[$namespaceURI] = $schema;
    }

    /**
     * @param string $location
     * @param mixed $classLoader
     *
     * @return string|null
     */
    protected function createSchema(string $location, $classLoader = null): string
    {
        $schema = ReflectUtil::getResource($location, $classLoader);
        return $schema;
    }

    abstract protected function createModelInstance(DomDocumentInterface $document): ModelInstanceInterface;
}
