<?php
declare(strict_types=1);

namespace Basilicom\DataQualityBundle;

use Basilicom\DataQualityBundle\Tools\Installer;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class DataQualityBundle extends AbstractPimcoreBundle
{
    /**
     * @return string[]|array
     */
    public function getJsPaths(): array
    {
        return [
            '/bundles/dataquality/js/pimcore/object/classes/layout/dataQuality.js',
            '/bundles/dataquality/js/DataQualityBundle.js'
        ];
    }

    /**
     * @return string[]|array
     */
    public function getCssPaths(): array
    {
        return [
            '/bundles/dataquality/css/admin.css'
        ];
    }

    /**
     * @return object|Installer
     */
    public function getInstaller(): Installer
    {
        return $this->container->get(Installer::class);
    }
}
