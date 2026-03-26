<?php
/**
 * @author Humberto González <humberto.gonzalez@balloon-group.com>
 */
declare(strict_types=1);

namespace BalloonGroup\Configurations\Helper;

use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\Helper\Context;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{
    /**
     * Data constructor
     *
     * @param Context $context
     * @param IndexerInterfaceFactory $indexerFactory
     * @param ConfigInterface $indexerConfig
     * @param TimezoneInterface $timezone
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        private IndexerInterfaceFactory $indexerFactory,
        private ConfigInterface $indexerConfig,
        private TimezoneInterface $timezone,
        private LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    /**
     * Send a full reindex
     *
     * @return void
     */
    public function sendFullReindex(): void
    {
        $this->logger->info("===== sendFullReindex =====");

        $indexers = $this->indexerConfig->getIndexers();

        foreach (array_keys($indexers) as $indexerId) {
            try {
                $dateTime = $this->timezone->date()->format('Y-m-d H:i:s');

                $this->logger->info("> DateTime: " . $dateTime);
                $this->logger->info("> Reindexing: " . $indexerId);
                $indexer = $this->indexerFactory->create()->load($indexerId);
                $indexer->reindexAll();
            } catch (\Exception $e) {
                $this->logger->error("[ERROR] Integration Full Reindex: " . $e->getMessage());
            }
        }
    }
}
