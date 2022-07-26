<?php

namespace Jabe\Model\Xml\Impl\Parser;

use Jabe\Model\Xml\ModelInstanceInterface;
use Jabe\Model\Xml\Exception\ModelValidationException;
use Jabe\Model\Xml\Impl\Util\{
    DomUtil,
    ReflectUtil
};
use Jabe\Model\Xml\Instance\DomDocumentInterface;

abstract class AbstractModelParser
{
    protected $schemas = [];

    /**
     * @param resource $inputStream
     */
    public function parseModelFromStream($inputStream): ModelInstanceInterface
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
            $dom->schemaValidate($schema);
        } catch (\Exception $e) {
            throw new ModelValidationException("Error during DOM document validation");
        }
    }

    protected function getSchema(DomDocumentInterface $document): ?string
    {
        $rootElement = $document->getRootElement();
        $namespaceURI = $rootElement->getNamespaceURI();
        if (array_key_exists($namespaceURI, $this->schemas)) {
            return $this->schemas[$namespaceURI];
        }
        return null;
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
    protected function createSchema(string $location, $classLoader = null): ?string
    {
        return ReflectUtil::getResource($location, $classLoader);
    }

    abstract protected function createModelInstance(DomDocumentInterface $document): ModelInstanceInterface;
}
