<?php

namespace Basilicom\DataQualityBundle\Model\Provider;

use Basilicom\DataQualityBundle\DefinitionsCollection\DefinitionsCollection;
use Pimcore\Model\DataObject\ClassDefinition\DynamicOptionsProvider\SelectOptionsProviderInterface;

class DefinitionsProvider implements SelectOptionsProviderInterface
{
    public function getOptions($context, $fieldDefinition): array
    {
        $options = [];
        foreach (DefinitionsCollection::getAllTypes() as $definitionKey => $definitionClass) {
            $options[] = [
                'value' => $definitionClass,
                'key'   => $definitionKey
            ];
        }

        return $options;
    }

    public function hasStaticOptions($context, $fieldDefinition): bool
    {
        return false;
    }

    public function getDefaultValue($context, $fieldDefinition): ?string
    {
        return $fieldDefinition->getDefaultValue();
    }
}
