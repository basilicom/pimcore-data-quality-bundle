<?php

namespace Basilicom\DataQualityBundle\Definition;

class NotEmptyDefinition extends DefinitionAbstract
{
    public function validate($content, string $fieldType, array $parameters): bool
    {
        switch ($fieldType) {
            case 'input':
            case 'textarea':
                $content = trim($content);

                return $content !== '';
            case 'numeric':
                return !(empty($content) && $content !== 0);
            default:
                return !empty($content);
        }
    }
}
