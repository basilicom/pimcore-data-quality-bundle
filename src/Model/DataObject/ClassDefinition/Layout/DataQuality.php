<?php

namespace Basilicom\DataQualityBundle\Model\DataObject\ClassDefinition\Layout;

use Pimcore\Model;
use Pimcore\Model\DataObject\ClassDefinition\Data\LayoutDefinitionEnrichmentInterface;
use Pimcore\Model\DataObject\ClassDefinition\Layout;

class DataQuality extends Layout implements LayoutDefinitionEnrichmentInterface
{
    public string $fieldtype = 'dataQuality';
    public string $html      = '';
    public int $dataQualityConfigId;

    /**
     * @return int
     */
    public function getDataQualityConfigId(): int
    {
        return $this->dataQualityConfigId;
    }

    /**
     * @param int $dataQualityConfigId
     */
    public function setDataQualityConfigId(int $dataQualityConfigId): void
    {
        $this->dataQualityConfigId = $dataQualityConfigId;
    }

    /**
     * @param Model\DataObject\Concrete $object
     * @param array $context additional contextual data
     *
     * @return self
     */
    public function enrichLayoutDefinition($object, $context = [])
    {
        return $this;
    }
}
