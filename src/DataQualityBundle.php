<?php
declare(strict_types=1);

namespace Basilicom\DataQualityBundle;

use Basilicom\DataQualityBundle\Tools\Installer;
use Exception;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;

class DataQualityBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait {
        getVersion as protected getComposerVersion;
    }

    public function getInstaller(): Installer
    {
        return $this->container->get(Installer::class);
    }

    protected function getComposerPackageName(): string
    {
        return 'basilicom/pimcore-data-quality-bundle';
    }

    public function getVersion(): string
    {
        try {
            return $this->getComposerVersion();
        } catch (Exception $exception) {
            return 'unknown';
        }
    }

    public function getJsPaths(): array
    {
        return [
            '/bundles/dataquality/js/pimcore/object/classes/layout/dataQuality.js',
            '/bundles/dataquality/js/pimcore/object/layout/dataQuality.js',
            '/bundles/dataquality/js/DataQualityBundle.js'
        ];
    }

    public function getCssPaths(): array
    {
        return [
            '/bundles/dataquality/css/admin.css'
        ];
    }
}
