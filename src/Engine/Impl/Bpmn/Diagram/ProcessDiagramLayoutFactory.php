<?php

namespace Jabe\Engine\Impl\Bpmn\Diagram;

use PHPixie\Image as PImage;
use PHPixie\Image\Drivers\Driver\Resource as ImageResource;
use Jabe\Engine\{
    ProcessEngineException,
    RepositoryServiceInterface
};
use Jabe\Engine\Impl\Bpmn\Parser\BpmnParser;
use Jabe\Engine\Impl\Context\Context;
use Jabe\Engine\Repository\{
    DiagramElement,
    DiagramLayout,
    DiagramNode
};
use Jabe\Model\Xml\Impl\Instance\{
    DomDocumentExt,
    DomElementExt
};

class ProcessDiagramLayoutFactory
{
    private const GREY_THRESHOLD = 175;

    // Parser features and their values needed to disable XXE Parsing
    private const XXE_FEATURES = [
        "http://apache.org/xml/features/disallow-doctype-decl" => true,
        "http://xml.org/sax/features/external-general-entities" => false,
        "http://xml.org/sax/features/external-parameter-entities" => false,
        "http://apache.org/xml/features/nonvalidating/load-external-dtd" => false
    ];

    /**
     * Provides positions and dimensions of elements in a process diagram as
     * provided by {@link RepositoryService#getProcessDiagram(String)}.
     *
     * Currently, it only supports BPMN 2.0 models.
     *
     * @param bpmnXmlStream
     *          BPMN 2.0 XML file
     * @param imageStream
     *          BPMN 2.0 diagram in PNG format (JPEG and other formats supported
     *          by {@link ImageIO} may also work)
     * @return Layout of the process diagram
     * @return null when parameter imageStream is null
     */
    public function getProcessDiagramLayout($bpmnXmlStream, $imageStream = null): ?DiagramLayout
    {
        $bpmnModel = $this->parseXml($bpmnXmlStream);
        return $this->getBpmnProcessDiagramLayout($bpmnModel, $imageStream);
    }

    /**
     * Provides positions and dimensions of elements in a BPMN process diagram as
     * provided by {@link RepositoryService#getProcessDiagram(String)}.
     *
     * @param bpmnModel
     *          BPMN 2.0 XML document
     * @param imageStream
     *          BPMN 2.0 diagram in PNG format (JPEG and other formats supported
     *          by {@link ImageIO} may also work)
     * @return Layout of the process diagram
     * @return null when parameter imageStream is null
     */
    public function getBpmnProcessDiagramLayout(DomDocumentExt $bpmnModel, $imageStream = null): ?DiagramLayout
    {
        if ($imageStream == null) {
            return null;
        }
        $diagramBoundsXml = $this->getDiagramBoundsFromBpmnDi($bpmnModel);
        $diagramBoundsImage = $this->getDiagramBoundsFromImage($imageStream);

        $listOfBounds = [];
        $listOfBounds[$diagramBoundsXml->getId()] = $diagramBoundsXml;
        $listOfBounds = array_merge($listOfBounds, $this->getElementBoundsFromBpmnDi($bpmnModel));

        $listOfBoundsForImage = $this->transformBoundsForImage($diagramBoundsImage, $diagramBoundsXml, $listOfBounds);
        return new DiagramLayout($listOfBoundsForImage);
    }

    protected function parseXml($bpmnXmlStream): DomDocumentExt
    {
        $bpmnModel = new DomDocumentExt();
        try {
            $meta = stream_get_meta_data($bpmnXmlStream);
            $bpmnModel->loadXML(fread($bpmnXmlStream, filesize($meta['uri'])));
        } catch (\Exception $e) {
            throw new ProcessEngineException("Error while parsing BPMN model.", $e);
        }
        return $bpmnModel;
    }

    protected function getDiagramBoundsFromBpmnDi(DomDocumentExt $bpmnModel): DiagramNode
    {
        $minX = null;
        $minY = null;
        $maxX = null;
        $maxY = null;

        // Node positions and dimensions
        $setOfBounds = $bpmnModel->getElementsByTagNameNS(BpmnParser::BPMN_DC_NS, "Bounds");
        for ($i = 0; $i < $setOfBounds->count(); $i += 1) {
            $element = $setOfBounds->item($i);
            $x = floatval($element->getAttribute("x"));
            $y = floatval($element->getAttribute("y"));
            $width = floatval($element->getAttribute("width"));
            $height = floatval($element->getAttribute("height"));

            if ($x == 0 && $y == 0 && $width == 0 && $height == 0) {
                // Ignore empty labels like the ones produced by Yaoqiang:
                // <bpmndi:BPMNLabel><dc:Bounds height="0.0" width="0.0" x="0.0" y="0.0"/></bpmndi:BPMNLabel>
            } else {
                if ($minX === null || $x < $minX) {
                    $minX = $x;
                }
                if ($minY === null || $y < $minY) {
                    $minY = $y;
                }
                if ($maxX === null || $maxX < ($x + $width)) {
                    $maxX = ($x + $width);
                }
                if ($maxY === null || $maxY < ($y + $height)) {
                    $maxY = ($y + $height);
                }
            }
        }

        // Edge bend points
        $waypoints = $bpmnModel->getElementsByTagNameNS(BpmnParser::OMG_DI_NS, "waypoint");
        for ($i = 0; $i < $waypoints->count(); $i += 1) {
            $waypoint = $waypoints->item($i);
            $x = floatval($waypoint->getAttribute("x"));
            $y = floatval($waypoint->getAttribute("y"));

            if ($minX === null || $x < $minX) {
                $minX = $x;
            }
            if ($minY === null || $y < $minY) {
                $minY = $y;
            }
            if ($maxX === null || $maxX < $x) {
                $maxX = $x;
            }
            if ($maxY === null || $maxY < $y) {
                $maxY = $y;
            }
        }

        $diagramBounds = new DiagramNode("BPMNDiagram");
        $diagramBounds->setX($minX);
        $diagramBounds->setY($minY);
        $diagramBounds->setWidth($maxX - $minX);
        $diagramBounds->setHeight($maxY - $minY);
        return $diagramBounds;
    }

    protected function getDiagramBoundsFromImage($resource, int $offsetTop = 0, int $offsetBottom = 0): ?DiagramNode
    {
        if (is_resource($resource)) {
            try {
                $meta = stream_get_meta_data($resource);
                $image = (new PImage())->read($meta['uri']);
            } catch (\Exception $e) {
                throw new ProcessEngineException("Error while reading process diagram image.", $e);
            }
            $diagramBoundsImage = $this->getDiagramBoundsFromImage($image, $offsetTop, $offsetBottom);
            return $diagramBoundsImage;
        } elseif ($resource instanceof ImageResource) {
            $width = $resource->width();
            $height = $resource->height();

            $rowIsWhite = [];
            $columnIsWhite = [];

            for ($row = 0; $row < $height; $row += 1) {
                if (!array_key_exists($row, $rowIsWhite)) {
                    $rowIsWhite[$row] = true;
                }
                if ($row <= $offsetTop || $row >  $resource->height() - $offsetBottom) {
                    $rowIsWhite[$row] = true;
                } else {
                    for ($column = 0; $column < $width; $column += 1) {
                        if (!array_key_exists($column, $columnIsWhite)) {
                            $columnIsWhite[$column] = true;
                        }
                        $pixel = $resource->getPixel($column, $row);
                        $color = $pixel->color();
                        $alpha = $pixel->opacity();
                        $red   = ($color >> 16) & 0xFF;
                        $green = ($color >>  8) & 0xFF;
                        $blue  = ($color >>  0) & 0xFF;
                        if (!($alpha == 0 || ($red >= self::GREY_THRESHOLD && $green >= self::GREY_THRESHOLD && $blue >= self::GREY_THRESHOLD))) {
                            $rowIsWhite[$row] = false;
                            $columnIsWhite[$column] = false;
                        }
                    }
                }
            }

            $marginTop = 0;
            for ($row = 0; $row < $height; $row += 1) {
                if (array_key_exists($row, $rowIsWhite) && $rowIsWhite[$row]) {
                    $marginTop += 1;
                } else {
                    // Margin Top Found
                    break;
                }
            }

            $marginLeft = 0;
            for ($column = 0; $column < $width; $column++) {
                if (array_key_exists($column, $columnIsWhite) && $columnIsWhite[$column]) {
                    $marginLeft += 1;
                } else {
                    // Margin Left Found
                    break;
                }
            }

            $marginRight = 0;
            for ($column = $width - 1; $column >= 0; $column -= 1) {
                if (array_key_exists($column, $columnIsWhite) && $columnIsWhite[$column]) {
                    $marginRight += 1;
                } else {
                    // Margin Right Found
                    break;
                }
            }

            $marginBottom = 0;
            for ($row = $height - 1; $row >= 0; $row -= 1) {
                if (array_key_exists($row, $rowIsWhite) && $rowIsWhite[$row]) {
                    $marginBottom += 1;
                } else {
                    // Margin Bottom Found
                    break;
                }
            }

            $diagramBoundsImage = new DiagramNode();
            $diagramBoundsImage->setX($marginLeft);
            $diagramBoundsImage->setY($marginTop);
            $diagramBoundsImage->setWidth($width - $marginRight - $marginLeft);
            $diagramBoundsImage->setHeight($height - $marginBottom - $marginTop);
            return $diagramBoundsImage;
        }
    }

    protected function getElementBoundsFromBpmnDi(DomDocumentExt $bpmnModel): array
    {
        $listOfBounds = [];
        // iterate over all DI shapes
        $shapes = $bpmnModel->getElementsByTagNameNS(BpmnParser::BPMN_DI_NS, "BPMNShape");
        for ($i = 0; $i < $shapes->count(); $i += 1) {
            $shape = $shapes->item($i);
            $bpmnElementId = $shape->getAttribute("bpmnElement");
            // get bounds of shape
            $childNodes = $shape->childNodes;
            for ($j = 0; $j < $childNodes->count(); $j += 1) {
                $childNode = $childNodes->item($j);
                if (
                    $childNode instanceof DomElementExt &&
                    BpmnParser::BPMN_DC_NS == $childNode->namespaceURI &&
                    $childNode->localName == "Bounds"
                ) {
                    $bounds = $this->parseBounds($childNode);
                    $bounds->setId($bpmnElementId);
                    $listOfBounds[$bpmnElementId] = $bounds;
                    break;
                }
            }
        }
        return $listOfBounds;
    }

    protected function parseBounds(DomElementExt $boundsElement): DiagramNode
    {
        $bounds = new DiagramNode();
        $bounds->setX(floatval($boundsElement->getAttribute("x")));
        $bounds->setY(floatval($boundsElement->getAttribute("y")));
        $bounds->setWidth(floatval($boundsElement->getAttribute("width")));
        $bounds->setHeight(floatval($boundsElement->getAttribute("height")));
        return $bounds;
    }

    protected function transformBoundsForImage(DiagramNode $diagramBoundsImage, DiagramNode $diagramBoundsXml, $elementBounds)
    {
        if ($elementBounds instanceof DiagramNode) {
            $scalingFactorX = $diagramBoundsImage->getWidth() / $diagramBoundsXml->getWidth();
            $scalingFactorY = $diagramBoundsImage->getWidth() / $diagramBoundsXml->getWidth();

            $elementBoundsForImage = new DiagramNode($elementBounds->getId());
            $elementBoundsForImage->setX(round(($elementBounds->getX() - $diagramBoundsXml->getX()) * $scalingFactorX + $diagramBoundsImage->getX()));
            $elementBoundsForImage->setY(round(($elementBounds->getY() - $diagramBoundsXml->getY()) * $scalingFactorY + $diagramBoundsImage->getY()));
            $elementBoundsForImage->setWidth(round($elementBounds->getWidth() * $scalingFactorX));
            $elementBoundsForImage->setHeight(round($elementBounds->getHeight() * $scalingFactorY));
            return $elementBoundsForImage;
        } elseif (is_array($elementBounds)) {
            $listOfBoundsForImage = [];
            foreach ($elementBounds as $key => $value) {
                $listOfBoundsForImage[$key] = $this->transformBoundsForImage($diagramBoundsImage, $diagramBoundsXml, $value);
            }
            return $listOfBoundsForImage;
        }
    }
}
