<?php

declare(strict_types=1);

namespace Basilicom\DataQualityBundle\Migrations;

use Basilicom\DataQualityBundle\Tools\Installer;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Pimcore\Model\DataObject\ClassDefinition;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class Version20220922102754 extends AbstractMigration implements ContainerAwareInterface
{
    private ContainerInterface $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getDescription(): string
    {
        return 'Update existing class';
    }

    public function up(Schema $schema): void
    {
        $class = ClassDefinition::getByName('DataQualityConfig');
        if ($class !== null) {
            /** @var Installer $installer */
            $installer = $this->container->get(Installer::class);
            $installer->install();
        }
    }

    public function down(Schema $schema): void
    {
        // Not necessary
    }
}
