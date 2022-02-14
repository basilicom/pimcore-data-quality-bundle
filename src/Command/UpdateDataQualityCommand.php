<?php

namespace Basilicom\DataQualityBundle\Command;

use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

use Basilicom\DataQualityBundle\Service\DataQualityService;
use Exception;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\DataQualityConfig;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\ClassDefinition;
use Symfony\Component\Process\Process;


class UpdateDataQualityCommand extends AbstractCommand
{
    private $batchLimit = 100;

    private DataQualityService $dataQualityService;

    public function __construct(
        string $name = null,
        DataQualityService $dataQualityService
    ) {
        $this->dataQualityService = $dataQualityService;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('dataquality:update')
            ->setAliases(['dq:update'])
            ->setDescription('Re-Compute and update dataquality on objects')
            ->addOption(
                'quality-config-id', null,
                InputOption::VALUE_REQUIRED,
                "Object-ID of a DataQualityConfiguration"
            )
            ->addOption(
                'batch-size', null,
                InputOption::VALUE_REQUIRED,
                "Number of object to process in one batch process"
            )
            ->addOption(
                'batch-number', null,
                InputOption::VALUE_REQUIRED,
                "The number of the batch to process."
            );
    }


    public function execute(InputInterface $input, OutputInterface $output)
    {
        $batchSize = (int)$input->getOption("batch-size");
        if ($batchSize > 0) {
            $this->batchLimit = $batchSize;
        }
        $batchNumber = (int)$input->getOption("batch-number");
        $qualityConfigId = (int)$input->getOption("quality-config-id");
        if ($batchNumber === 0) {
            $this->executeMainProcess($qualityConfigId);
        } else {
            $this->executeBatchProcess($batchNumber, $qualityConfigId, $this->batchLimit);
        }
        return 0;
    }

    /**
     * Spawn child processes (commands) to update the DataQuality in batches
     * @param $qualityConfigId
     * @return void
     */
    protected function executeMainProcess($qualityConfigId)
    {
        $batchNumber = 1;
        do {
            echo "Processing batch #" . $batchNumber . " ... ";
            $commandPrefix = 'env php '.realpath(PIMCORE_PROJECT_ROOT.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'console');
            chdir(PIMCORE_PROJECT_ROOT);

            $consoleCommand = 'dataquality:update'
                . ' --batch-size='.$this->batchLimit
                . ' --batch-number='.$batchNumber
                . ' --quality-config-id='.$qualityConfigId;

            exec($commandPrefix.' '.$consoleCommand, $output, $retval);
            $batchNumber++;
            # spawn batch sub-process
            echo "OK\n";
        } while ($retval == 0);
    }

    /**
     * Update the DataQuality of a batch of objects
     * @param $batchNumber
     * @param $qualityConfigId
     * @return void
     * @throws Exception
     */
    protected function executeBatchProcess($batchNumber = 1,$qualityConfigId)
    {
        $offset = ($batchNumber-1)*$this->batchLimit;

        $dataQualityConfig = DataQualityConfig::getById($qualityConfigId);

        if (!is_object($dataQualityConfig)) {
            return;
        }

        $classId   = $dataQualityConfig->getDataQualityClass();
        $fieldname = $dataQualityConfig->getDataQualityField();
        if (empty($classId) || empty($fieldname)) {
            return;
        }

        $class = ClassDefinition::getById($classId);
        if (empty($class)) {
            return;
        }

        $classListing = '\\Pimcore\\Model\\DataObject\\' . $class->getName() . '\\Listing';
        $list         = new $classListing();
        $list->setObjectTypes([AbstractObject::OBJECT_TYPE_OBJECT, AbstractObject::OBJECT_TYPE_VARIANT]);
        $list->setUnpublished(true);
        $list->setOffset($offset);
        $list->setLimit($this->batchLimit);
        $list->setOrderKey("oo_id");
        $list->setOrder("asc");
        $list->load();

        if ($list->getCount() <= 0) {
            exit(1);
            return;
        }

        foreach ($list as $item) {
            $this->dataQualityService->calculateDataQuality($item, $dataQualityConfig);
        }
    }

}
