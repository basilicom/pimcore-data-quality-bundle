<?php
declare(strict_types=1);

namespace Basilicom\DataQualityBundle\Model\Provider;

use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\ClassDefinition\DynamicOptionsProvider\SelectOptionsProviderInterface;

class ObjectTypeProvider implements SelectOptionsProviderInterface
{
    /**
     * @param array $context
     * @param ClassDefinition\Data $fieldDefinition
     * @return array
     */
    public function getOptions($context, $fieldDefinition)
    {
        $result = [];
        $classDefinitionList = (new ClassDefinition\Listing())->getClasses();

        /** @var $item ClassDefinition */
        foreach ($classDefinitionList as $item) {
            $result[] = [
                'key' => $item->getName() . ' (' . $item->getId() . ')',
                'value' => $item->getId() . '@@@' . $item->getName(),
            ];
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
