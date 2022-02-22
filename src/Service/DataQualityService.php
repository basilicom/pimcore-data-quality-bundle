<?php

namespace Basilicom\DataQualityBundle\Service;

use Basilicom\DataQualityBundle\Provider\DataQualityProvider;
use Basilicom\DataQualityBundle\View\DataQualityViewModel;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\DataQualityConfig;

class DataQualityService
{
    private DataQualityProvider $dataQualityProvider;

    public function __construct(DataQualityProvider $dataQualityProvider)
    {
        $this->dataQualityProvider = $dataQualityProvider;
    }

    /**
     * @return DataQualityConfig[]
     */
    public function getDataQualityConfigs(?AbstractObject $dataObject): array
    {
        return $this->dataQualityProvider->getDataQualityConfigs($dataObject);
    }

    public function calculateDataQuality(AbstractObject $dataObject, DataQualityConfig $dataQualityConfig): DataQualityViewModel
    {
        $setting = $this->temporarilyEnableInheritance();

        $data = $this->dataQualityProvider->calculateDataQuality($dataObject, $dataQualityConfig);

        $this->restoreInheritance($setting);

        return $data;
    }

    private function temporarilyEnableInheritance(): bool
    {
        $oldInheritedValuesSetting = AbstractObject::getGetInheritedValues();
        AbstractObject::setGetInheritedValues(true);

        return $oldInheritedValuesSetting;
    }

    private function restoreInheritance(bool $oldInheritedValuesSetting)
    {
        AbstractObject::setGetInheritedValues($oldInheritedValuesSetting);
    }
}
