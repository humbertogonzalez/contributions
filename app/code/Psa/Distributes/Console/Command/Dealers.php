<?php

declare(strict_types=1);

namespace BalloonGroup\Distributes\Console\Command;

use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Config\ScopeConfigInterface;
use BalloonGroup\Distributes\Helper\Dealers\Process;
use Psr\Log\LoggerInterface;

class Dealers extends Command
{
    /**
     * Dealers constructor
     *
     * @param State $appState
     * @param ScopeConfigInterface $scopeConfig
     * @param Process $process
     */
    public function __construct(
        protected State $appState,
        protected ScopeConfigInterface $scopeConfig,
        protected Process $process,
        protected LoggerInterface $logger
    ) {
        parent::__construct();
    }

    /**
     * Initialization of the command.
     */
    protected function configure()
    {
        $this->setName('ballon:core:processdealers');
        $this->setDescription('Comando para procesar los Dealers recibidos en servicio');
        parent::configure();
    }

    /**
     * CLI command description.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('> Begin Dealers process...');
        $this->appState->setAreaCode(Area::AREA_FRONTEND);

        try {
            $this->process->loadAndSaveDealers();
        } catch (Exception $e) {
            $output->writeln('> ERROR Dealers Command');
            $this->logger->critical("> ERROR Dealers Command: " . $e->getMessage());
        }

        $output->writeln('> End Begin Dealers process...');
    }
}
