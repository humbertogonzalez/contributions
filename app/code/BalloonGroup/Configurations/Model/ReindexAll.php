<?php
/**
 * @author Humberto González <humberto.gonzalez@balloon-group.com>
 */
declare(strict_types=1);

namespace BalloonGroup\Configurations\Model;

use BalloonGroup\Configurations\Api\ReindexAllInterface;
use BalloonGroup\Configurations\Helper\Data;
use Psr\Log\LoggerInterface;

class ReindexAll implements ReindexAllInterface
{
    /**
     * ReindexAll constructor
     *
     * @param Data $configurationsData
     * @param LoggerInterface $logger
     */
    public function __construct(
        private Data $configurationsData,
        private LoggerInterface $logger
    ) {

    }

    public function execute(): bool {
        $this->logger->info("===== ReindexAll::execute =====");

        try {
            $this->configurationsData->sendFullReindex();

            return true;
        } catch (\Exception $e) {
            $this->logger->error("[ERROR] Scheduled Integration Reindex: " . $e->getMessage());

            return false;
        }
    }
}
