<?php

namespace Basilicom\DataQualityBundle\Model\DataObject\ClassDefinition\Layout;

use Pimcore\Model\DataObject\ClassDefinition\Data\LayoutDefinitionEnrichmentInterface;
use Pimcore\Model\DataObject\ClassDefinition\Layout;

class DataQuality extends Layout implements LayoutDefinitionEnrichmentInterface
{
    public string $fieldtype = 'dataQuality';
    public string $html = '';
    public int $dataQualityConfigId;

    public function getDataQualityConfigId(): int
    {
        return $this->dataQualityConfigId;
    }

    public function setDataQualityConfigId($dataQualityConfigId): void
    {
        $this->dataQualityConfigId = (int)$dataQualityConfigId;
    }

    public function enrichLayoutDefinition($object, array $context = []): static
    {
        return $this;
    }
}
