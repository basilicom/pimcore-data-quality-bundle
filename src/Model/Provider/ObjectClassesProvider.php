<?php
declare(strict_types=1);

namespace Basilicom\DataQualityBundle\Model\Provider;

use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\ClassDefinition\DynamicOptionsProvider\SelectOptionsProviderInterface;
use Pimcore\Model\DataObject\DataQualityConfig;

class ObjectClassesProvider implements SelectOptionsProviderInterface
{
    /**
     * @param array $context
     * @param ClassDefinition\Data $fieldDefinition
     *
     * @return array
     */
    public function getOptions($context, $fieldDefinition): array
    {
        $object = null;
        if (isset($context['object'])) {
            $object = $context['object'];
        }

        if (empty($object) || !($object instanceof DataQualityConfig)) {
            return [];
        }

        $result              = [];
        $classDefinitionList = (new ClassDefinition\Listing())->getClasses();

        foreach ($classDefinitionList as $item) {
            if ($item->getId() === $object->getClassId()) {
                continue;
            }

            $result[] = [
                'key'   => $item->getName() . ' (' . $item->getId() . ')',
                'value' => $item->getId()
            ];
        }

        return $result;
    }

    /**
     * @param array $context
     * @param ClassDefinition\Data $fieldDefinition
     *
     * @return string|null
     */
    public function getDefaultValue($context, $fieldDefinition): ?string
    {
        return $fieldDefinition->getDefaultValue();
    }

    /**
     * @param array $context
     * @param ClassDefinition\Data $fieldDefinition
     *
     * @return bool
     */
    public function hasStaticOptions($context, $fieldDefinition): bool
    {
        return false;
    }
}
