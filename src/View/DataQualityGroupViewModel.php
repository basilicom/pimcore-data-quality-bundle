<?php

namespace Basilicom\DataQualityBundle\View;

class DataQualityGroupViewModel
{
    private string $name;

    /** @var DataQualityFieldViewModel[] */
    private array $fields;

    public function __construct(string $name, array $fields)
    {
        $this->name   = $name;
        $this->fields = $fields;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return DataQualityFieldViewModel[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
