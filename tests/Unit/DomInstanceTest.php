<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use BpmPlatform\Model\Xml\Impl\Instance\DomDocumentImpl;
use BpmPlatform\Model\Xml\Impl\Util\DomUtil;

class DomInstanceTest extends TestCase
{
    private const XML1 = '<?xml version="1.0" encoding="UTF-8"?>

    <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    
    <xsl:template match="/">
    <html>
    <body>
      <h2>My CD Collection</h2>
      <table border="1">
        <tr>
          <th style="text-align:left">Title</th>
          <th style="text-align:left">Artist</th>
        </tr>
        <xsl:for-each select="catalog/cd">
        <tr>
          <td><xsl:value-of select="title"/></td>
          <td><xsl:value-of select="artist"/></td>
        </tr>
        </xsl:for-each>
      </table>
    </body>
    </html>
    </xsl:template>
    
    </xsl:stylesheet>';

    public function testDomDocumentCreation(): void
    {
        $document = DomUtil::parseInputStream(self::XML1);
        $rootElement = $document->getRootElement();
        $namespaceURI = $rootElement->getNamespaceURI();
        $this->assertEquals('http://www.w3.org/1999/XSL/Transform', $namespaceURI);
    }
}
