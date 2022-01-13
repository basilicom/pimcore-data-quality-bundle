<?php

namespace Basilicom\DataQualityBundle\Definition;

use Pimcore\Model\DataObject\ClassDefinition\Data;

class MinimumStringLengthDefinition extends DefinitionAbstract
{
    const NECESSARY_PARAMETER_COUNT = 1;

    /**
     * @throws DefinitionException
     */
    public function validate($content, Data $fieldDefinition, array $parameters): bool
    {
        $fieldName = $fieldDefinition->getName();
        $fieldType = $fieldDefinition->getFieldtype();

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
                throw new DefinitionException('fieldtype ' . $fieldType . ' of field ' . $fieldName . ' is not supported.');
        }
    }
}
