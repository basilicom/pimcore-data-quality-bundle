<?php

namespace Basilicom\DataQualityBundle\Command;

use Basilicom\DataQualityBundle\Exception\DataQualityException;
use Basilicom\DataQualityBundle\Exception\NoDataObjectsAvailableException;
use Basilicom\DataQualityBundle\Service\DataQualityService;
use Exception;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\DataQualityConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateDataQualityCommand extends AbstractCommand
{
    const STOP_CHILD_PROCESS = 987;

    protected static $defaultName        = 'dataquality:update';
    protected static $defaultDescription = 'Re-compute and update data quality on objects.';

    private int $batchSize = 100;

    private DataQualityService $dataQualityService;

    public function __construct(
        DataQualityService $dataQualityService
    ) {
        $this->dataQualityService = $dataQualityService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setAliases(['dq:update'])
            ->addArgument(
                'quality-config-id',
                InputArgument::REQUIRED,
                'Object-ID of a DataQualityConfig'
            )
            ->addArgument(
                'batch-size',
                InputArgument::REQUIRED,
                'Number of object to process in one batch process'
            )
            ->addOption(
                'batch-number',
                null,
                InputOption::VALUE_OPTIONAL,
                'The number of the batch to process.'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $batchSize = (int)$input->getArgument('batch-size');
            if ($batchSize > 0) {
                $this->batchSize = $batchSize;
            }

            $batchNumber     = (int)$input->getOption('batch-number');
            $qualityConfigId = (int)$input->getArgument('quality-config-id');
            if ($batchNumber === 0) {
                $this->executeMainProcess($qualityConfigId);
            } else {
                $this->executeBatchProcess($qualityConfigId, $batchNumber);
            }
        } catch (NoDataObjectsAvailableException $exception) {
            $this->output->writeln('Processing finished.');

            return self::STOP_CHILD_PROCESS;
        } catch (Exception $exception) {
            $this->output->writeln('Exception: ' . $exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Spawn child processes (commands) to update the DataQuality in batches
     */
    protected function executeMainProcess(int $qualityConfigId)
    {
        $batchNumber = 1;
        do {
            $commandPrefix = 'env php '. realpath(PIMCORE_PROJECT_ROOT.DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR.'console');
            chdir(PIMCORE_PROJECT_ROOT);

            $consoleCommand = $this->getName()
                . ' --batch-number=' . $batchNumber
                . ' ' . $qualityConfigId
                . ' ' . $this->batchSize;

            $output = [];
            exec($commandPrefix . ' ' . $consoleCommand, $output, $resultCode);

            foreach ($output as $line) {
                $this->output->writeln($line);
            }

            $batchNumber++;
        } while ($resultCode == 0);
    }

    /**
     * Update the DataQuality of a batch of objects
     *
     * @throws NoDataObjectsAvailableException
     * @throws DataQualityException
     */
    protected function executeBatchProcess(int $qualityConfigId, int $batchNumber)
    {
        $this->output->write('Processing batch #' . $batchNumber . ' ... ');

        $offset = ($batchNumber - 1) * $this->batchSize;

        $dataQualityConfig = DataQualityConfig::getById($qualityConfigId);

        if (!is_object($dataQualityConfig)) {
            throw new DataQualityException('The data quality config does not exist.');
        }

        $classId   = $dataQualityConfig->getDataQualityClass();
        $fieldname = $dataQualityConfig->getDataQualityField();
        if (empty($classId) || empty($fieldname)) {
            throw new DataQualityException('The data quality config is not configured correctly. Missing class and field.');
        }

        $class = ClassDefinition::getById($classId);
        if (empty($class)) {
            throw new DataQualityException('The chosen class in the data quality config does not exist.');
        }

        $classListing = '\\Pimcore\\Model\\DataObject\\' . $class->getName() . '\\Listing';
        $list         = new $classListing();
        $list->setObjectTypes([AbstractObject::OBJECT_TYPE_OBJECT, AbstractObject::OBJECT_TYPE_VARIANT]);
        $list->setUnpublished(true);
        $list->setOffset($offset);
        $list->setLimit($this->batchSize);
        $list->setOrderKey('oo_id');
        $list->setOrder('asc');
        $list->load();

        if ($list->getCount() <= 0) {
            throw new NoDataObjectsAvailableException('There are no data objects left.');
        }

        foreach ($list as $item) {
            $this->dataQualityService->calculateDataQuality($item, $dataQualityConfig);
        }

        $this->output->writeln('OK - ' . (($batchNumber - 1) * $this->batchSize) + $list->getCount());

        if ($list->getCount() < $this->batchSize) {
            throw new NoDataObjectsAvailableException('There are no data objects left.');
        }
    }
}
