<?php

namespace Basilicom\DataQualityBundle\Model\Listener;

use Basilicom\DataQualityBundle\Service\DataQualityService;
use Exception;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\DataQualityConfig;

class ObjectPostUpdateListener
{
    private DataQualityService $dataQualityService;

    public function __construct(
        DataQualityService $dataQualityService
    ) {
        $this->dataQualityService = $dataQualityService;
    }

    public function onPostUpdate(ElementEventInterface $event)
    {
        $arguments = $event->getArguments();
        if (isset($arguments['isAutoSave']) && $arguments['isAutoSave']) {
            return;
        }

        if (!$event instanceof DataObjectEvent) {
            return;
        }

        $object = $event->getElement();
        if ($object instanceof DataQualityConfig) {
            try {
                $this->updateAllDataObject($object);
            } catch (Exception $exception) {
                // do nothing
            }
        }
    }

    /**
     * @throws Exception
     */
    private function updateAllDataObject(DataQualityConfig $dataQualityConfig): void
    {
        $classId   = $dataQualityConfig->getDataQualityClass();
        $fieldname = $dataQualityConfig->getDataQualityField();
        if (empty($classId) || empty($fieldname)) {
            return;
        }

        $class = ClassDefinition::getById($classId);
        if (empty($class)) {
            return;
        }

        $classListing = '\\Pimcore\\Model\\DataObject\\' . $class->getName() . '\\Listing';
        $list         = new $classListing();
        $list->setObjectTypes([AbstractObject::OBJECT_TYPE_OBJECT, AbstractObject::OBJECT_TYPE_VARIANT]);
        $list->setUnpublished(true);
        $list->load();

        if ($list->getCount() <= 0) {
            return;
        }

        foreach ($list as $item) {
            $this->dataQualityService->calculateDataQuality($item, $dataQualityConfig);
        }
    }
}
