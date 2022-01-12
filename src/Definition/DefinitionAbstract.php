<?php

namespace Basilicom\DataQualityBundle\Definition;

abstract class DefinitionAbstract implements DefinitionInterface
{
    const NECESSARY_PARAMETER_COUNT = 0;

    protected array $parameters = [];

    public function getNecessaryParameterCount(): int
    {
        return static::NECESSARY_PARAMETER_COUNT;
    }

    public function validate($content, string $fieldType, array $parameters): bool
    {
        return false;
    }

    public function setParameters(array $parameters)
    {
        // bastodo: check if this is needed
        if (count($parameters) < $this->getNecessaryParameterCount()) {
            throw new DefinitionException(
                'Not enough parameters. ' .
                'Given ' . count($parameters) . ', necessary are ' . $this->getNecessaryParameterCount(),
                DefinitionException::NOT_ENOUGH_PARAMETERS
            );
        }

        $this->parameters = $parameters;
    }
}
