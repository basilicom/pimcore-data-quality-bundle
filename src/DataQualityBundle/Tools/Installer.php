<?php
declare(strict_types=1);

namespace Basilicom\DataQualityBundle\Tools;

use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Migrations\Version;
use Doctrine\DBAL\Schema\Schema;
use Pimcore\Db\ConnectionInterface;
use Pimcore\Extension\Bundle\Installer\MigrationInstaller;
use Pimcore\Migrations\Migration\InstallMigration;
use Pimcore\Migrations\MigrationManager;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\ClassDefinition\Service;
use Pimcore\Model\DataObject\Objectbrick;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class Installer extends MigrationInstaller
{
    /** @var string */
    private $installSourcesPath;

    /** @var array */
    private $classesToInstall = [
        'DataQualityConfig' => 'DQC',
    ];

    /**
     * @param BundleInterface $bundle
     * @param ConnectionInterface $connection
     * @param MigrationManager $migrationManager
     */
    public function __construct(
        BundleInterface $bundle,
        ConnectionInterface $connection,
        MigrationManager $migrationManager
    ) {
        $this->installSourcesPath = __DIR__ . '/../Resources/install';

        parent::__construct($bundle, $connection, $migrationManager);
    }

    /**
     * @param Schema $schema
     * @param Version $version
     *
     * @return void
     *
     * @throws \Exception
     */
    public function migrateInstall(Schema $schema, Version $version): void
    {
        /** @var InstallMigration $migration */
        $migration = $version->getMigration();

        if ($migration->isDryRun()) {
            $this->outputWriter->write('<fg=cyan>DRY-RUN:</> Skipping installation');

            return;
        }

        $this->installClasses();
        $this->installObjectBricks();
    }

    /**
     * @param Schema $schema
     * @param Version $version
     *
     * @return void
     */
    public function migrateUninstall(Schema $schema, Version $version): void
    {
        /** @var InstallMigration $migration */
        $migration = $version->getMigration();

        if ($migration->isDryRun()) {
            $this->outputWriter->write('<fg=cyan>DRY-RUN:</> Skipping uninstallation');

            return;
        }

        $this->uninstallClasses();
        $this->uninstallObjectBricks();
    }

    private function getClassesToInstall(): array
    {
        $result = [];
        foreach (array_keys($this->classesToInstall) as $className) {
            $filename = sprintf('class_%s_export.json', $className);
            $path = $this->installSourcesPath . '/class_sources/' . $filename;
            $path = realpath($path);

            if (false === $path || !is_file($path)) {
                throw new AbortMigrationException(sprintf(
                    'Class export for class "%s" was expected in "%s" but file does not exist',
                    $className,
                    $path
                ));
            }

            $result[$className] = $path;
        }

        return $result;
    }

    private function installClasses()
    {
        $classes = $this->getClassesToInstall();
        $mapping = $this->classesToInstall;

        foreach ($classes as $key => $path) {
            $class = ClassDefinition::getByName($key);

            if ($class) {
                $this->outputWriter->write(sprintf(
                    '     <comment>WARNING:</comment> Skipping class "%s" as it already exists',
                    $key
                ));

                continue;
            }

            $class = new ClassDefinition();
            $classId = $mapping[$key];

            $class->setName($key);
            $class->setId($classId);

            $data = file_get_contents($path);
            $success = Service::importClassDefinitionFromJson($class, $data, false, true);

            if (!$success) {
                throw new AbortMigrationException(sprintf(
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

            $this->outputWriter->write(sprintf(
                '     <comment>WARNING:</comment> Skipping class "%s" as it doesn\'t exists',
                $key
            ));
        }
    }

    private function installObjectBricks()
    {
        $bricks = $this->findInstallFiles(
            $this->installSourcesPath . '/objectbrick_sources',
            '/^objectbrick_(.*)_export\.json$/'
        );

        foreach ($bricks as $key => $path) {
            if ($brick = Objectbrick\Definition::getByKey($key)) {
                $this->outputWriter->write(sprintf(
                    '     <comment>WARNING:</comment> Skipping object brick "%s" as it already exists',
                    $key
                ));

                continue;
            }

            $brick = new Objectbrick\Definition();
            $brick->setKey($key);

            $data = file_get_contents($path);
            $success = Service::importObjectBrickFromJson($brick, $data);

            if (!$success) {
                throw new AbortMigrationException(sprintf(
                    'Failed to create object brick "%s"',
                    $key
                ));
            }
        }
    }

    private function uninstallObjectBricks()
    {
        $bricks = $this->findInstallFiles(
            $this->installSourcesPath . '/objectbrick_sources',
            '/^objectbrick_(.*)_export\.json$/'
        );

        foreach ($bricks as $key => $path) {
            if ($brick = Objectbrick\Definition::getByKey($key)) {
                $brick->delete();

                continue;
            }

            $this->outputWriter->write(sprintf(
                '     <comment>WARNING:</comment> Skipping object brick "%s" as it doesn\'t exists',
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
            if (preg_match($pattern, $file->getFilename(), $matches)) {
                $key = $matches[1];
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
