<?php

namespace Basilicom\DataQualityBundle\Service;

use Basilicom\DataQualityBundle\Provider\DataQualityProvider;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\DataQualityConfig;

class DataQualityService
{
    /** @var DataQualityProvider */
    private $dataQualityProvider;

    public function __construct(DataQualityProvider $dataQualityProvider)
    {
        $this->dataQualityProvider = $dataQualityProvider;
    }

    public function getDataQualityConfig(AbstractObject $dataObject): ?DataQualityConfig
    {
        return $this->dataQualityProvider->getDataQualityConfig($dataObject);
    }

    public function getDataQualityRule(DataQualityConfig $dataQualityConfig): ?DataQualityConfig\DataQulalityRule
    {
        return $this->dataQualityProvider->getDataQualityRule($dataQualityConfig);
    }

    public function getDataQualityFields(AbstractObject $dataObject, array $dataFields): array
    {
        return $this->dataQualityProvider->getDataQualityFields($dataObject, $dataFields);
    }

    public function getDataQualityData(AbstractObject $dataObject, DataQualityConfig\DataQulalityRule $dataQualityRule): array
    {
        return $this->dataQualityProvider->getDataQualityData($dataObject, $dataQualityRule);
    }
}
