<?php

namespace Basilicom\DataQualityBundle\Definition;

class MinimumStringLengthDefinition extends DefinitionAbstract
{
    const NECESSARY_PARAMETER_COUNT = 1;

    public function validate($content, string $fieldType, array $parameters): bool
    {
        switch ($fieldType) {
            case 'input':
            case 'textarea':
            case 'number':
                $content = trim($content);

                if (empty($parameters)) {
                    $length = 0;
                } else {
                    $length = $parameters[0];
                }

                if (mb_strlen($content) < $length) {
                    return false;
                }

                return true;
            default:
                // wrong field type
                return false;
        }
    }
}
