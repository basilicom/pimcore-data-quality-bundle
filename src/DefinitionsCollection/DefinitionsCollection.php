<?php

namespace Basilicom\DataQualityBundle\DefinitionsCollection;

use Basilicom\DataQualityBundle\Definition\MinimumStringLengthDefinition;
use Basilicom\DataQualityBundle\Definition\NotEmptyDefinition;

class DefinitionsCollection
{
    const TYPES = [
        'Not Empty'             => NotEmptyDefinition::class,
        'Minimum String Length' => MinimumStringLengthDefinition::class,
    ];

    public static function getAllTypes(): array
    {
        return self::TYPES;
    }
}
