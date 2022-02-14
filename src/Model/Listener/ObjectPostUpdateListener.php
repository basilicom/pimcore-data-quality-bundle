<?php

namespace Basilicom\DataQualityBundle\Model\Listener;

use Basilicom\DataQualityBundle\Exception\DataQualityException;
use Basilicom\DataQualityBundle\Service\DataQualityService;
use Exception;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\DataQualityConfig;

class ObjectPostUpdateListener
{
    private static $listenerEnabled = true;

    private DataQualityService $dataQualityService;

    public function __construct(
        DataQualityService $dataQualityService
    ) {
        $this->dataQualityService = $dataQualityService;
    }

    public function onPostUpdate(ElementEventInterface $event)
    {

        // skip if temporarily (in-process) disabled (to prevent recursion)
        if (!self::$listenerEnabled) {
            return;
        }

        // skip on outosave
        $arguments = $event->getArguments();
        if (isset($arguments['isAutoSave']) && $arguments['isAutoSave']) {
            return;
        }

        if (!$event instanceof DataObjectEvent) {
            return;
        }

        // skip for system (non-backend) user (imports, other processes)
        $userId = 1;
        $user = \Pimcore\Tool\Admin::getCurrentUser();
        if ($user) {
            $userId = $user->getId();
        }
        if ($userId == 1) {
            return; // skip if no- or system user
        }

        $dataObject = $event->getElement();

        // skip all but "real" data objects (no folders!)
        if (!($dataObject instanceof \Pimcore\Model\DataObject\Concrete)) {
            return;
        }

        // skip if no data quality configartions exist
        $dataQualityConfigs = $this->dataQualityService->getDataQualityConfigs($dataObject);
        if (empty($dataQualityConfigs)) {
            return; // no data quality configurations
        }

        self::$listenerEnabled = false; // prevent recursion!
        foreach ($dataQualityConfigs as $dataQualityConfig) {
            $this->dataQualityService->calculateDataQuality($dataObject, $dataQualityConfig);
        }
        self::$listenerEnabled = true;
    }
}
