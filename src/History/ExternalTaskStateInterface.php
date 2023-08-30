<?php

namespace Jabe\History;

interface ExternalTaskStateInterface
{
    public static function created(): ExternalTaskStateInterface;

    public static function failed(): ExternalTaskStateInterface;

    public static function successful(): ExternalTaskStateInterface;

    public static function deleted(): ExternalTaskStateInterface;
}
