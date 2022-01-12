<?php

namespace Basilicom\DataQualityBundle\Definition;

interface DefinitionInterface
{
    /**
     * @param $content
     * @param string $fieldType
     * @param array $parameters
     *
     * @return bool
     */
    public function validate($content, string $fieldType, array $parameters): bool;

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
