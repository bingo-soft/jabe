<?php

namespace BpmPlatform\Model\Xml\Impl\Util;

class QName
{

    private $qualifier;
    private $localName;

    public function __construct(?string $qualifier, string $localName)
    {
        $this->qualifier = $qualifier;
        $this->localName = $localName;
    }

    public function getQualifier(): ?string
    {
        return $this->qualifier;
    }

    public function getLocalName(): string
    {
        return $this->localName;
    }

    public static function parseQName(string $identifier): QName
    {
        $split = preg_split("/:/", $identifier, 2);
        if (count($split) == 2) {
            $qualifier = $split[0];
            $localName = $split[1];
        } else {
            $qualifier = null;
            $localName = $split[0];
        }
        return new QName($qualifier, $localName);
    }

    public static function combine(?string $qualifier, string $localName): string
    {
        if (empty($qualifier)) {
            return $localName;
        } else {
            return $qualifier . ":" . $localName;
        }
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return self::combine($this->qualifier, $this->localName);
    }
}
