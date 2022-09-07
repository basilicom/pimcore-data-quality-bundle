<?php

namespace Basilicom\DataQualityBundle\View;

class DataQualityFieldViewModel
{
    private string $name;
    private int $weight;
    private bool $valid;
    private ?string $language;
    private ?array $validFields;

    public function __construct(string $name, int $weight, bool $valid, ?string $language = null, ?array $validFields = null)
    {
        $this->name        = $name;
        $this->weight      = $weight;
        $this->valid       = $valid;
        $this->language    = $language;
        $this->validFields = $validFields;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getWeight(): int
    {
        return $this->weight;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }

    /**
     * @return string|null
     */
    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * @return array|null
     */
    public function getValidFields(): ?array
    {
        return $this->validFields;
    }
}
