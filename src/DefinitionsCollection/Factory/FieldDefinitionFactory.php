<?php

namespace Basilicom\DataQualityBundle\DefinitionsCollection\Factory;

use Basilicom\DataQualityBundle\Definition\DefinitionInterface;
use Basilicom\DataQualityBundle\DefinitionsCollection\FieldDefinition;
use Pimcore\Model\DataObject\Fieldcollection\Data\DataQualityFieldDefinition;

class FieldDefinitionFactory
{
    const DEFAULT_GROUP = '__default__';

    public function get(DataQualityFieldDefinition $definition): FieldDefinition
    {
        list($fieldName, $title) = explode('@@@', $definition->getField());

        if (strpos($title, '###')) {
            list($title, $language) = explode('###', $title);
        }

        return new FieldDefinition(
            $this->getClass($definition->getCondition()),
            $fieldName,
            $title,
            empty($definition->getWeight()) ? 0 : (int) $definition->getWeight(),
            $this->parameterStringToArray($definition->getParameters()),
            $language ?? null
        );
    }

    private function parameterStringToArray(string $parameterString): array
    {
        $parameters      = [];
        $parameterString = trim($parameterString);
        if (empty($parameterString)) {
            return $parameters;
        }

        foreach (str_getcsv($parameterString, ';') as $parameterItem) {
            $parameters[] = trim($parameterItem);
        }

        return $parameters;
    }

    private function getClass(?string $conditionClass): ?DefinitionInterface
    {
        if (!class_exists($conditionClass)) {
            return null;
        }

        return new $conditionClass();
    }
}
