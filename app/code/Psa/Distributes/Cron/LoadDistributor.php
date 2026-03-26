<?php
namespace BalloonGroup\Distributes\Cron;

use BalloonGroup\Distributes\Helper\Dealers\Process;
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
