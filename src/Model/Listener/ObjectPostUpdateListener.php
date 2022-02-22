<?php

namespace Basilicom\DataQualityBundle\Model\Listener;

use Basilicom\DataQualityBundle\Service\DataQualityService;
use Exception;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Tool\Admin;

class ObjectPostUpdateListener
{
    private static bool $listenerEnabled = true;

    private DataQualityService $dataQualityService;

    public function __construct(
        DataQualityService $dataQualityService
    ) {
        $this->dataQualityService = $dataQualityService;
    }

    public function onPostUpdate(ElementEventInterface $event)
    {
        try {
            $this->listenerIsEnabled();
            $this->isAutoSave($event->getArguments());
            $this->isEventOfCorrectType($event);
            $this->isBackendUserActive();

            $dataObject = $event->getElement();

            if (!($dataObject instanceof Concrete)) {
                throw new Exception('skip all but "real" data objects (no folders)');
            }

            $dataQualityConfigs = $this->dataQualityService->getDataQualityConfigs($dataObject);
            if (empty($dataQualityConfigs)) {
                return; // no data quality configurations
            }

            self::$listenerEnabled = false;
            foreach ($dataQualityConfigs as $dataQualityConfig) {
                $this->dataQualityService->calculateDataQuality($dataObject, $dataQualityConfig);
            }
            self::$listenerEnabled = true;
        } catch (Exception $exception) {
            // just skip
        }
    }

    /**
     * @throws Exception
     */
    private function listenerIsEnabled()
    {
        if (!self::$listenerEnabled) {
            throw new Exception('skip if temporarily (in-process) disabled (to prevent recursion)');
        }
    }

    /**
     * @throws Exception
     */
    private function isAutoSave(array $arguments)
    {
        if (isset($arguments['isAutoSave']) && $arguments['isAutoSave']) {
            throw new Exception('skip on outosave');
        }
    }

    /**
     * @throws Exception
     */
    private function isEventOfCorrectType(ElementEventInterface $event)
    {
        if (!$event instanceof DataObjectEvent) {
            throw new Exception('wrong event type');
        }
    }

    /**
     * @throws Exception
     */
    private function isBackendUserActive()
    {
        $userId = 0;
        $user   = Admin::getCurrentUser();
        if ($user) {
            $userId = $user->getId();
        }
        if ($userId === 0) {
            throw new Exception('skip for system (non-backend) user (imports, other processes)');
        }
    }
}
