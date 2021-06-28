<?php

namespace Basilicom\DataQualityBundle\Model\Listener;

use Basilicom\DataQualityBundle\Service\DataQualityService;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Event\Model\ElementEventInterface;

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

        try {

        } catch (\Exception $exception) {

        }
    }
}
