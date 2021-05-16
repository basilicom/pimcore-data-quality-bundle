<?php
declare(strict_types=1);

namespace Basilicom\DataQualityBundle\Provider;

use Pimcore\Model\DataObject;

final class DataQualityProvider
{
    public function getDataQuality(DataObject $dataObject, array $dataFields): array
    {
        $objectData = [];

        foreach ($dataFields as $fieldName) {
            $value = null;
            $getter = 'get' . ucfirst($fieldName);

            if (\method_exists(
                '\\Pimcore\\Model\\DataObject\\' . $dataObject->getClassName(),
                'get' . ucfirst($fieldName)
            )) {
                $value = $dataObject->$getter();
            }

            $objectData[] = [
                'name' => $fieldName,
                'isEmpty' => empty($value)
            ];
        }

        return $objectData;
    }
}
