<?php
declare(strict_types=1);

namespace Basilicom\DataQualityBundle\Provider;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\DataQualityConfig;

final class DataQualityProvider
{
    private const DATA_QUALITY_PERCENT = 'DataQualityPercent';

    public function setDataQualityPercent(AbstractObject $dataObject, array $data): int
    {
        $value = 0;
        $setter = 'set' . \ucfirst(self::DATA_QUALITY_PERCENT);

        if (\method_exists(
            '\\Pimcore\\Model\\DataObject\\' . $dataObject->getClassName(),
            $setter
        )) {
            $dataObject->$setter();
            $dataObject->save();
        }

        return $value;
    }

    public function getDataQualityPercent(AbstractObject $dataObject): int
    {
        $value = 0;
        $getter = 'get' . \ucfirst(self::DATA_QUALITY_PERCENT);

        if (\method_exists(
            '\\Pimcore\\Model\\DataObject\\' . $dataObject->getClassName(),
            $getter
        )) {
            $value = $dataObject->$getter();
        }

        return $value;
    }

    public function getDataQualityConfig(AbstractObject $dataObject): ?DataQualityConfig
    {
        $dataQualityConfigList = new DataQualityConfig\Listing();

        foreach ($dataQualityConfigList as $dataQualityConfig) {
            $dataQualityType = $dataQualityConfig->getDataQualityType();
            if ($dataObject && $dataObject->getClassId() === $dataQualityType) {
                return $dataQualityConfig;
            }
        }

        return null;
    }


    public function getDataQualityRule(DataQualityConfig $dataQualityConfig): ?DataQualityConfig\DataQulalityRule
    {
        return $dataQualityConfig->getDataQulalityRule();
    }

    public function getDataQualityFields(AbstractObject $dataObject, array $dataFields): array
    {
        $objectData = [];

        foreach ($dataFields as $fieldName) {
            $value = null;
            $fieldNameSplit = \explode('@@@', $fieldName);
            $getter = 'get' . \ucfirst($fieldNameSplit[0]);

            if (\method_exists(
                '\\Pimcore\\Model\\DataObject\\' . $dataObject->getClassName(),
                $getter
            )) {
                $value = $dataObject->$getter();
            }

            $objectData[] = [
                'name' => $fieldNameSplit[1] ?: $fieldNameSplit[0],
                'isEmpty' => empty($value)
            ];
        }

        return $objectData;
    }

    public function getDataQualityData(AbstractObject $dataObject, DataQualityConfig\DataQulalityRule $dataQualityRule): array
    {
        $data = ['items' => []];

        foreach ($dataQualityRule->getItems() as $dataQualityRuleItem) {
            if ($dataQualityRuleItem instanceof ObjectCompletion) {
                foreach ($dataQualityRuleItem->getArea() as $area) {
                    $data['items'][] = [
                        'name' => $area['AreaName']->getData(),
                        'fields' => $this->getDataQualityFields($dataObject, $area['AreaFields']->getData())
                    ];
                }
            }
        }

        $data['percent'] = 0;

        return $data;
    }
}
