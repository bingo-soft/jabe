<?php

namespace Jabe\Impl\Model\Wsdl\Impl;

class WsdlModelConstants
{
    /** The XSI namespace */
    public const XSI_NS = "http://www.w3.org/2001/XMLSchema-instance";

    public const XS_NS = "http://www.w3.org/2001/XMLSchema";

    /** The WSDL namespace */
    public const WSDL_NS = "http://schemas.xmlsoap.org/wsdl/";

    /** The SOAP namespace */
    public const SOAP_NS = "http://schemas.xmlsoap.org/wsdl/soap/";

    /** The location of the WSDL XML schema. */
    public const WSDL_SCHEMA_LOCATION = "src/Model/Wsdl/Resources/wsdl.xsd";

    public const MODEL_NAME = 'wsdl';

    // elements ////////////////////////////////////////
    public const SOAP_ELEMENT_ADDRESS = "address";

    public const WSDL_ELEMENT_BASE_ELEMENT = "baseElement";
    public const WSDL_ELEMENT_BINDING = "binding";
    public const WSDL_ELEMENT_COMPLEX_TYPE = "complexType";
    public const WSDL_ELEMENT_DEFINITIONS = "definitions";
    public const WSDL_ELEMENT_ELEMENT = "element";
    public const WSDL_ELEMENT_OPERATION = "operation";
    public const WSDL_ELEMENT_PORT = "port";
    public const WSDL_ELEMENT_ROOT_ELEMENT = "rootElement";
    public const WSDL_ELEMENT_SCHEMA = "schema";
    public const WSDL_ELEMENT_SEQUENCE = "sequence";
    public const WSDL_ELEMENT_SERVICE = "service";
    public const WSDL_ELEMENT_TYPES = "types";

    // attributes ////////////////////////////////////////
    public const SOAP_ATTRIBUTE_LOCATION = "location";
    public const WSDL_ATTRIBUTE_BINDING = "binding";
    public const WSDL_ATTRIBUTE_NAME = "name";
    public const WSDL_ATTRIBUTE_TARGET_NAMESPACE = "targetNamespace";
    public const WSDL_ATTRIBUTE_TYPE = "type";
}
