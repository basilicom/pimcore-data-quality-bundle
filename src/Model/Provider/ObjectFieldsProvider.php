<?php
declare(strict_types=1);

namespace Basilicom\DataQualityBundle\Model\Provider;

use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\ClassDefinition\Data\Localizedfields;
use Pimcore\Model\DataObject\ClassDefinition\DynamicOptionsProvider\SelectOptionsProviderInterface;
use Pimcore\Model\DataObject\DataQualityConfig;

class ObjectFieldsProvider implements SelectOptionsProviderInterface
{
    /**
     * @param array $context
     * @param ClassDefinition\Data $fieldDefinition
     * @return array
     */
    public function getOptions($context, $fieldDefinition)
    {
        $result = [];
        $object = isset($context["object"]) ? $context["object"] : null;

        if ($object === null) {
            return $result;
        }

        /** @var DataQualityConfig $object */
        $dataQualityTypeId = $object->getDataQualityType();
        $classType = ClassDefinition::getById($dataQualityTypeId);

        if ($classType === null) {
            return $result;
        }

        $fieldDefinitions = $classType->getFieldDefinitions();

        foreach ($fieldDefinitions as $field) {
            $name = $field->getName();

            if ($name === 'localizedfields') {
                /** @var Localizedfields $field */
                $children = $field->getChildren();

                foreach ($children as $child) {
                    $result[] = [
                        'key' => $child->getTitle() ?: $child->getName(),
                        'value' => $child->getName(),
                    ];
                }

            } else {
                $result[] = [
                    'key' => $field->getTitle() ?: $name,
                    'value' => $name,
                ];
            }
        }

        return $result;
    }

    /**
     * @param array $context
     * @param ClassDefinition\Data $fieldDefinition
     * @return mixed
     */
    public function getDefaultValue($context, $fieldDefinition)
    {
        return $fieldDefinition->getDefaultValue();
    }

    /**
     * @param array $context
     * @param ClassDefinition\Data $fieldDefinition
     * @return bool
     */
    public function hasStaticOptions($context, $fieldDefinition)
    {
        return false;
    }
}
