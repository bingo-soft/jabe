<?php

namespace Jabe\Model\Bpmn\Instance;

interface DefinitionsInterface extends BpmnModelElementInstanceInterface
{
    public function getId(): ?string;

    public function setId(string $id): void;

    public function getName(): ?string;

    public function setName(string $name): void;

    public function getTargetNamespace(): string;

    public function setTargetNamespace(string $namespace): void;

    public function getExpressionLanguage(): string;

    public function setExpressionLanguage(string $expressionLanguage): void;

    public function getTypeLanguage(): string;

    public function setTypeLanguage(string $typeLanguage): void;

    public function getExporter(): ?string;

    public function setExporter(string $exporter): void;

    public function getExporterVersion(): ?string;

    public function setExporterVersion(string $exporterVersion): void;

    public function getImports(): array;

    public function addImport(ImportInterface $import): void;

    public function getExtensions(): array;

    public function getRootElements(): array;

    public function addRootElement(RootElementInterface $element): void;

    public function removeRootElement(RootElementInterface $element): void;

    public function getBpmDiagrams(): array;

    public function getRelationships(): array;
}
