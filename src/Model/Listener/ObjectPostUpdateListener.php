<?php

namespace Basilicom\DataQualityBundle\Model\Listener;

use Basilicom\DataQualityBundle\Provider\DataQualityProvider;
use Basilicom\DataQualityBundle\Service\DataQualityService;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\DataQualityConfig;
use Pimcore\Model\Listing\AbstractListing;

class ObjectPostUpdateListener
{
    /** @var DataQualityService */
    private $dataQualityService;

    public function __construct(
        DataQualityService $dataQualityService
    ) {
        $this->dataQualityService = $dataQualityService;
    }

    /**
     * @param ElementEventInterface $element
     */
    public function onPostUpdate(ElementEventInterface $element)
    {
        if (!$element instanceof DataObjectEvent) {
            return;
        }

        $object = $element->getElement();

        if ($object instanceof DataObject\DataQualityConfig) {
            try {
                $dataQualityTypeId = $object->getDataQualityType();

                if ($dataQualityTypeId === null) {
                    return;
                }

                $classType = ClassDefinition::getById($dataQualityTypeId);

                if ($classType === null) {
                    return;
                }

                $dataQualityRule = $this->dataQualityService->getDataQualityRule($object);

                if ($dataQualityRule === null) {
                    return;
                }

                $className = $classType->getName();
                $class = '\\Pimcore\\Model\\DataObject\\' . $classType->getName() . '\\Listing';
                $list = new $class();
                $list->setObjectTypes([DataObject::OBJECT_TYPE_OBJECT, DataObject::OBJECT_TYPE_VARIANT]);
                $list->setUnpublished(true);
                $list->load();

                $getter = 'get' . \ucfirst(DataQualityProvider::DATA_QUALITY_PERCENT);

                foreach ($list as $item) {
                    if (\method_exists(
                        '\\Pimcore\\Model\\DataObject\\' . $className,
                        $getter
                    )) {
                        $this->dataQualityService->getDataQualityData($item, $dataQualityRule);
                    }
                }

            } catch (\Exception $exception) {
            }

            return;
        }
    }
}
