<?php
namespace Jof\Import\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Class ImportCommand
 *
 * @package Jof\Import\Console\Command
 */
class ImportCommand extends Command
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\State $state
    ) {
        $this->objectManager = $objectManager;
        $this->state = $state;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('customer:import')
            ->setDescription('Import customer')
            ->addArgument('filename', InputArgument::REQUIRED, "CSV file path");
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $import = $this->getImportModel();
       

        try {
            $import->setFile(realpath($input->getArgument('filename')));
            $result = $import->execute();
            if ($result) {
                $output->writeln('<info>The import was successful.</info>');
                $output->writeln("Log trace:");
                $output->writeln($import->getFormattedLogTrace());
            } else {
                $output->writeln('<error>Import failed.</error>');
                $errors = $import->getErrors();
                foreach ($errors as $error) {
                    $output->writeln('<error>' . $error->getErrorMessage() . ' - ' .$error->getErrorDescription() . '</error>');
                }
            }

        } catch (FileNotFoundException $e) {
            $output->writeln('<error>File not found.</error>');

        } catch (\InvalidArgumentException $e) {
            $output->writeln('<error>Invalid source.</error>');
            $output->writeln("Log trace:");
            $output->writeln($import->getFormattedLogTrace());
        }
    }

    /**
     * @return \Jof\Import\Model\Import
     */
    protected function getImportModel()
    {
        $this->state->setAreaCode('adminhtml');
        return $this->objectManager->create('Jof\Import\Model\Import');
    }
}

