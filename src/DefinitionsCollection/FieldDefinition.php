<?php

namespace Basilicom\DataQualityBundle\DefinitionsCollection;

use Basilicom\DataQualityBundle\Definition\DefinitionInterface;

class FieldDefinition
{
    protected DefinitionInterface $conditionClass;
    protected string $fieldName;
    protected string $title;
    protected int $weight;
    protected array $parameters;

    public function __construct(DefinitionInterface $conditionClass, string $fieldName, string $title, int $weight, array $parameters)
    {
        $this->conditionClass = $conditionClass;
        $this->fieldName      = $fieldName;
        $this->title          = $title;
        $this->weight         = $weight;
        $this->parameters     = $parameters;
    }

    /**
     * @return DefinitionInterface
     */
    public function getConditionClass(): DefinitionInterface
    {
        return $this->conditionClass;
    }

    /**
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return int
     */
    public function getWeight(): int
    {
        return $this->weight;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
