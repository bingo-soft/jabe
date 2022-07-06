<?php

namespace Jabe\Engine\Impl\Cxf\Webservice;

use Jabe\Engine\Impl\Bpmn\Data\SimpleStructureDefinition;
use Jabe\Engine\Impl\Bpmn\Parser\XMLImporterInterface;
use Jabe\Engine\Impl\Util\Xml\Element;
use Jabe\Engine\Impl\Webservice\{
    WSDLManager,
    WSOperation,
    WSService,
    WSDLServiceBuilder
};
use Jabe\Model\Wsdl\Instance\{
    ComplexTypeInterface,
    DefinitionsInterface,
    OperationInterface,
    ServiceInterface,
    TypesInterface
};

class CxfWSDLImporter implements XMLImporterInterface
{
    protected $wsServices = [];
    protected $wsOperations = [];
    protected $structures = [];

    protected $wsdlLocation;
    protected $namespace = "";

    public function importFrom($data): void
    {
        if ($data instanceof Element) {
            $this->namespace = $data->attribute("namespace") === null ? "" : $data->attribute("namespace") . ":";
            $this->importFrom($data->attribute("location"));
        } elseif (is_string($data)) {
            $url = $data;

            $this->wsServices = [];
            $this->wsOperations = [];
            $this->structures = [];
            $this->wsdlLocation = $url;

            $wsdlManager = new WSDLManager();
            $def = $wsdlManager->getDefinition($url);
            $builder = new WSDLServiceBuilder();

            $services = $builder->buildServices($def);
            foreach ($services as $service) {
                $wsService = $this->importService($service, $def);
                $this->wsServices[$this->namespace . $wsService->getName()] = $wsService;
            }

            if ($def !== null && $def->getTypes() !== null) {
                $this->importTypes($def->getTypes());
            }
        }
    }

    protected function importService(ServiceInterface $service, DefinitionsInterface $def): WSService
    {
        $name = $service->getName();
        $location = $service->getEndpoint();

        $serviceName = $service->getName();
        $arr = explode(':', $service->getBinding());
        $bindingName = end($arr);
        $bindings = $def->getBindings();
        $binding = null;
        $wsService = new WSService($this->namespace . $name, $location, $this->wsdlLocation);
        foreach ($bindings as $testBinding) {
            $arr = explode(':', $testBinding->getType());
            $bindingType = end($arr);
            if ($serviceName == $bindingType && $bindingName == $testBinding->getName()) {
                $operations = $testBinding->getOperations();
                foreach ($operations as $operation) {
                    $wsOperation = $this->importOperation($operation, $wsService);
                    $wsService->addOperation($wsOperation);
                    $this->wsOperations[$this->namespace . $operation->getName()] = $wsOperation;
                }
            }
        }

        return $wsService;
    }

    protected function importOperation(OperationInterface $operation, WSService $service): WSOperation
    {
        $wsOperation = new WSOperation($this->namespace . $operation->getName(), $operation->getName(), $service);
        return $wsOperation;
    }

    protected function importTypes(TypesInterface $types): void
    {
        $impl = $types->getSchema();

        $mappings = $impl->getElements();

        foreach ($mappings as $mapping) {
            $this->importStructure($mapping);
        }
    }

    protected function importStructure(ComplexTypeInterface $mapping): void
    {
        $structure = new SimpleStructureDefinition($this->namespace . $mapping->getName());
        $params = $mapping->getParameters();
        foreach ($params as $param) {
            $fieldName = $param->getName();
            $arr = explode(":", $param->getType());
            $fieldClass = end($arr);
            $structure->setFieldName($fieldName, $fieldClass, null);
        }
        $this->structures[$structure->getId()] = $structure;
    }

    public function getStructures(): array
    {
        return $this->structures;
    }

    public function getServices(): array
    {
        return $this->wsServices;
    }

    public function getOperations(): array
    {
        return $this->wsOperations;
    }
}
