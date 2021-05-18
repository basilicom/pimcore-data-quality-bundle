<?php

namespace Basilicom\DataQualityBundle\Service;

use Basilicom\DataQualityBundle\Provider\DataQualityProvider;
use Pimcore\Model\DataObject;

class DataQualityService
{
    /** @var DataQualityProvider */
    private $dataQualityProvider;

    public function __construct(DataQualityProvider $dataQualityProvider)
    {
        $this->dataQualityProvider = $dataQualityProvider;
    }

    public function getDataQualityStatus(DataObject $dataObject, $dataFields): array
    {
        return $this->dataQualityProvider->getDataQuality($dataObject, $dataFields);
    }
}
