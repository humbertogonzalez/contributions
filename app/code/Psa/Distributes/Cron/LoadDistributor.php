<?php
namespace Psa\Distributes\Cron;

use Psa\Distributes\Helper\Dealers\Process;
use Psr\Log\LoggerInterface;

class LoadDistributor
{
    public function __construct(
        protected readonly Process $_process,
        protected LoggerInterface $logger
    ) {

    }

    public function execute(): void
    {
        $this->_process->loadAndSaveDealers();
    }
}
