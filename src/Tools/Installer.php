<?php
declare(strict_types=1);

namespace Basilicom\DataQualityBundle\Tools;

use Pimcore\Extension\Bundle\Installer\Exception\InstallationException;
use Pimcore\Extension\Bundle\Installer\SettingsStoreAwareInstaller;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\ClassDefinition\Service;
use Pimcore\Model\DataObject\Fieldcollection;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class Installer extends SettingsStoreAwareInstaller
{
    private string $installSourcesPath;
    private array $classesToInstall = [
        'DataQualityConfig' => 'DQC',
    ];

    public function __construct(
        BundleInterface $bundle,
    ) {
        $this->installSourcesPath = __DIR__ . '/../Resources/install';

        parent::__construct($bundle);
    }

    public function install(): void
    {
        $this->installFieldCollection();
        $this->installClasses();

        parent::install();
    }

    public function uninstall(): void
    {
        $this->uninstallClasses();
        $this->uninstallFieldCollection();

        parent::uninstall();
    }

    private function getClassesToInstall(): array
    {
        $result = [];
        foreach (\array_keys($this->classesToInstall) as $className) {
            $filename = \sprintf('class_%s_export.json', $className);
            $path     = $this->installSourcesPath . '/class_sources/' . $filename;
            $path     = \realpath($path);

            if (false === $path || !\is_file($path)) {
                throw new InstallationException(\sprintf(
                    'Class export for class "%s" was expected in "%s" but file does not exist',
                    $className,
                    $path
                ));
            }

            $result[$className] = $path;
        }

        return $result;
    }

    public function installClasses()
    {
        $classes = $this->getClassesToInstall();
        $mapping = $this->classesToInstall;

        foreach ($classes as $key => $path) {
            $class = ClassDefinition::getByName($key);
            if ($class === null) {
                $class = new ClassDefinition();
                $classId = $mapping[$key];

                $class->setName($key);
                $class->setId($classId);
            }

            $data    = \file_get_contents($path);
            $success = Service::importClassDefinitionFromJson($class, $data, false, true);

            if (!$success) {
                throw new InstallationException(\sprintf(
                    'Failed to create class "%s"',
                    $key
                ));
            }
        }
    }

    private function uninstallClasses()
    {
        $classes = $this->getClassesToInstall();

        foreach ($classes as $key => $path) {
            $class = ClassDefinition::getByName($key);

            if ($class) {
                $class->delete();

                continue;
            }

            $this->getOutput()->write(\sprintf(
                '     <comment>WARNING:</comment> Skipping class "%s" as it doesn\'t exists',
                $key
            ));
        }
    }

    private function installFieldCollection()
    {
        $fieldcollections = $this->findInstallFiles(
            $this->installSourcesPath . '/fieldcollection_sources',
            '/^fieldcollection_(.*)_export\.json$/'
        );

        foreach ($fieldcollections as $key => $path) {
            if (Fieldcollection\Definition::getByKey($key)) {
                $this->getOutput()->write(\sprintf(
                    '     <comment>WARNING:</comment> Skipping fieldcollection "%s" as it already exists',
                    $key
                ));

                continue;
            }

            $fieldcollection = new Fieldcollection\Definition();
            $fieldcollection->setKey($key);

            $data    = \file_get_contents($path);
            $success = Service::importFieldCollectionFromJson($fieldcollection, $data);

            if (!$success) {
                throw new InstallationException(\sprintf(
                    'Failed to create object fieldcollection "%s"',
                    $key
                ));
            }
        }
    }

    private function uninstallFieldCollection()
    {
        $fieldcollections = $this->findInstallFiles(
            $this->installSourcesPath . '/fieldcollection_sources',
            '/^fieldcollection_(.*)_export\.json$/'
        );

        foreach ($fieldcollections as $key => $path) {
            if ($fieldcollection = Fieldcollection\Definition::getByKey($key)) {
                $fieldcollection->delete();

                continue;
            }

            $this->getOutput()->write(sprintf(
                '     <comment>WARNING:</comment> Skipping fieldcollection "%s" as it doesn\'t exists',
                $key
            ));
        }
    }

    /**
     * @param string $directory
     * @param string $pattern
     *
     * @return array
     */
    private function findInstallFiles(string $directory, string $pattern): array
    {
        $finder = new Finder();
        $finder->files()->in($directory)->name($pattern);

        $results = [];
        foreach ($finder as $file) {
            if (\preg_match($pattern, $file->getFilename(), $matches)) {
                $key           = $matches[1];
                $results[$key] = $file->getRealPath();
            }
        }

        return $results;
    }

    /**
     * @return bool
     */
    public function needsReloadAfterInstall(): bool
    {
        return true;
    }
}
