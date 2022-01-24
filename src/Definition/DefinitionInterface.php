<?php

namespace Basilicom\DataQualityBundle\Definition;

use Pimcore\Model\DataObject\ClassDefinition\Data;

interface DefinitionInterface
{
    /**
     * @param $content
     * @param Data $fieldDefinition
     * @param array $parameters
     *
     * @return bool
     *
     * @throws DefinitionException
     */
    public function validate($content, Data $fieldDefinition, array $parameters): bool;

    /**
     * @return int
     */
    public function getNecessaryParameterCount(): int;

    /**
     * @param array $parameters
     *
     * @throws DefinitionException
     */
    public function setParameters(array $parameters);
}
