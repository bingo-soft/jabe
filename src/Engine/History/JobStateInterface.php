<?php

namespace BpmPlatform\Engine\History;

interface JobStateInterface
{
    public static function created(): JobStateInterface;

    public static function failed(): JobStateInterface;

    public static function successful(): JobStateInterface;

    public static function deleted(): JobStateInterface;
}
