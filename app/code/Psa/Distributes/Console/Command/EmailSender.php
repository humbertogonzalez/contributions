<?php

namespace Psa\Distributes\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Service\OrderService;
class EmailSender extends Command
{

    protected $helperData;
    protected $appState;

    public function __construct(
        State $appState,
        ScopeConfigInterface $scopeConfig,
        protected readonly OrderService $orderService,

    ) {

        $this->_appState = $appState;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct();

    }
    /**
     * Initialization of the command.
     */
    protected function configure()
    {
        $this->setName('ballon:core:emailsender');
        $this->setDescription('comando para probar la funcionalidad del cron para sincronizar ordenes con el sistema de legado');
        parent::configure();
    }

    /**
     * CLI command description.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->_appState->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

        // if ($this->_scopeConfig->getValue(WorkflowType::CONFIG_NAME_PATH) === WorkflowType::CLIENT_SIDE_COMPILATION) {
        //     throw new LocalizedException(__('Client side compilation is not supported for this command.'));
        // }
        $this->orderService->notify(112);
    }
}
