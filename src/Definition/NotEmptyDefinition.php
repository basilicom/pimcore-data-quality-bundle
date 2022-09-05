<?php

namespace Basilicom\DataQualityBundle\Definition;

use Pimcore\Model\DataObject\ClassDefinition\Data;

class NotEmptyDefinition extends DefinitionAbstract
{
    public function validate($content, Data $fieldDefinition, array $parameters): bool
    {
        $fieldType = $fieldDefinition->getFieldtype();

        switch ($fieldType) {
            case 'input':
            case 'textarea':
                $content = trim($content);

                return $content !== '';
            case 'numeric':
                return !(empty($content) && $content !== 0);
            case 'quantityValue':
                return !empty($content->getValue());
            default:
                return !empty($content);
        }
    }
}
