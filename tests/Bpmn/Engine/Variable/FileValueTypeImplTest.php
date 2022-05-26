<?php

namespace Tests\Bpmn\Engine\Variable;

use PHPUnit\Framework\TestCase;
use Jabe\Engine\Variable\Variables;
use Jabe\Engine\Variable\Impl\Type\FileValueTypeImpl;
use Jabe\Engine\Variable\Value\FileValueInterface;
use Jabe\Engine\Variable\Value\TypedValueInterface;
use Jabe\Model\Xml\Impl\Util\IoUtil;

class FileValueTypeImplTest extends TestCase
{
    private $type;

    protected function setUp(): void
    {
        $this->type = new FileValueTypeImpl();
    }

    public function testNameShouldBeFile(): void
    {
        $this->assertEquals("file", $this->type->getName());
    }

    public function testShouldNotHaveParent(): void
    {
        $this->assertNull($this->type->getParent());
    }

    public function testIsPrimitiveValue(): void
    {
        $this->assertTrue($this->type->isPrimitiveValueType());
    }

    public function testIsNotAnAbstractType(): void
    {
        $this->assertFalse($this->type->isAbstract());
    }

    public function testCanNotConvertFromAnyValue(): void
    {
        // we just use null to make sure false is always returned
        $this->assertFalse($this->type->canConvertFromTypedValue(null));
    }

    public function testConvertingThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->type->convertFromTypedValue(Variables::untypedNullValue());
    }

    public function testCreateValueFromFile(): void
    {
        $path = 'tests/Bpmn/Engine/Variable/Resources/simpleFile.txt';
        $file = fopen($path, 'r+');
        $value = $this->type->createValue($file, [FileValueTypeImpl::VALUE_INFO_FILE_NAME => $path]);
        $this->assertTrue($value instanceof FileValueInterface);
        $this->assertTrue($value->getType() instanceof FileValueTypeImpl);
        $this->checkStreamFromValue($value, "text");
    }

    public function testCreateValueFromObject(): void
    {
        $path = 'tests/Bpmn/Engine/Variable/Resources/simpleFile.txt';
        $this->expectException(\InvalidArgumentException::class);
        $this->type->createValue(new \stdClass(), [FileValueTypeImpl::VALUE_INFO_FILE_NAME => $path]);
    }

    public function testCreateValueWithProperties(): void
    {
        // given
        $path = 'tests/Bpmn/Engine/Variable/Resources/simpleFile.txt';
        $file = fopen($path, 'r+');
        $properties = [];
        $properties["filename"] = $path;
        $properties["mimeType"] = "someMimeType";
        $properties["encoding"] = "someEncoding";

        $value = $this->type->createValue($file, $properties);

        $this->assertTrue($value instanceof FileValueInterface);
        $this->assertEquals($path, $value->getFilename());
        $this->assertEquals("someMimeType", $value->getMimeType());
        $this->assertEquals("someEncoding", $value->getEncoding());
    }

    public function testMimeTypeSetToNull(): void
    {
        // given
        $path = 'tests/Bpmn/Engine/Variable/Resources/simpleFile.txt';
        $file = fopen($path, 'r+');
        $properties = [];
        $properties["filename"] = $path;
        $properties["mimeType"] = null;
        $properties["encoding"] = "someEncoding";
        $this->expectException(\InvalidArgumentException::class);
        $value = $this->type->createValue($file, $properties);
    }

    public function testEncodingSetToNull(): void
    {
        // given
        $path = 'tests/Bpmn/Engine/Variable/Resources/simpleFile.txt';
        $file = fopen($path, 'r+');
        $properties = [];
        $properties["filename"] = $path;
        $properties["mimeType"] = "someMimeType";
        $properties["encoding"] = null;
        $this->expectException(\InvalidArgumentException::class);
        $value = $this->type->createValue($file, $properties);
    }

    public function testCannotCreateFileWithoutName(): void
    {
        $path = 'tests/Bpmn/Engine/Variable/Resources/simpleFile.txt';
        $file = fopen($path, 'r+');
        $this->expectException(\InvalidArgumentException::class);
        $this->type->createValue($file, []);
    }

    public function testCannotCreateFileWithoutValueInfo(): void
    {
        $path = 'tests/Bpmn/Engine/Variable/Resources/simpleFile.txt';
        $file = fopen($path, 'r+');
        $this->expectException(\InvalidArgumentException::class);
        $this->type->createValue($file, null);
    }

    public function testCannotCreateFileWithInvalidTransientFlag(): void
    {
        $path = 'tests/Bpmn/Engine/Variable/Resources/simpleFile.txt';
        $file = fopen($path, 'r+');
        $info = [];
        $info["filename"] = $path;
        $info["transient"] = "foo";
        $this->expectException(\InvalidArgumentException::class);
        $this->type->createValue($file, $info);
    }

    public function testValueInfoContainsFileTypeNameTransientFlagAndEncoding(): void
    {
        $fileName = 'tests/Bpmn/Engine/Variable/Resources/simpleFile.txt';
        $file = fopen($fileName, 'r+');
        $fileType = "text/plain";
        $encoding = "UTF-8";
        $fileValue = Variables::fileValue($fileName)->file($file)->mimeType($fileType)
                     ->encoding($encoding)->setTransient(true)->create();
        $info = $this->type->getValueInfo($fileValue);
        $this->assertContains($fileName, $info);
        $this->assertContains($fileType, $info);
        $this->assertContains($encoding, $info);
        $this->assertContains(true, $info);
    }

    public function testFileByteArrayIsEqualToFileValueContent(): void
    {
        $fileName = 'tests/Bpmn/Engine/Variable/Resources/simpleFile.txt';
        $file = fopen($fileName, 'r+');
        $fileValue = Variables::fileValue($fileName)->file($file)->create();
        $this->assertEquals(IoUtil::getStringFromInputStream(fopen($fileName, 'r+')), $fileValue->getByteArray());
    }

    public function testDoesNotHaveParent(): void
    {
        $this->assertNull($this->type->getParent());
    }

    private function checkStreamFromValue(TypedValueInterface $value, string $expected): void
    {
        $this->assertEquals($expected, $value->getByteArray());
    }
}
