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
}
