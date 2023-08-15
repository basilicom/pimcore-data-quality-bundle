<?php

namespace Basilicom\DataQualityBundle\EventSubscriber;

use Pimcore\Event\BundleManager\PathsEvent;
use Pimcore\Event\BundleManagerEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PimcoreAdminSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            BundleManagerEvents::JS_PATHS => 'onJsPaths',
            BundleManagerEvents::CSS_PATHS => 'onCssPaths',
        ];
    }

    public function onJsPaths(PathsEvent $event): void
    {
        $event->setPaths(
            array_merge(
                $event->getPaths(),
                [
                    '/bundles/dataquality/js/pimcore/object/classes/layout/dataQuality.js',
                    '/bundles/dataquality/js/pimcore/object/layout/dataQuality.js',
                    '/bundles/dataquality/js/DataQualityBundle.js'
                ]
            )
        );
    }

    public function onCssPaths(PathsEvent $event): void
    {
        $event->setPaths(
            array_merge(
                $event->getPaths(),
                [
                    '/bundles/dataquality/css/admin.css'
                ]
            )
        );
    }
}
