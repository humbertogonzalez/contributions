<?php

namespace BalloonGroup\AmastyRequestQuote\Block\Email\Grid;

use Amasty\RequestQuote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\User\Model\UserFactory;

class Quote extends \Amasty\RequestQuote\Block\Email\Grid\Quote
{
    protected $quoteRepository;

    public function __construct(
        QuoteCollectionFactory $quoteCollectionFactory,
        Context $context,
        CartRepositoryInterface $cartRepository,
        ResourceConnection $resourceConnection,
        UserFactory $userFactory,
        array $data = []
    ) {
        parent::__construct($quoteCollectionFactory, $context, $data);
        $this->quoteRepository = $cartRepository;
        $this->resourceConnection = $resourceConnection;
        $this->userFactory = $userFactory;
    }
    public function getResellerId($quoteId)
    {
        $quote = $this->quoteRepository->get($quoteId);
        $customerId = $quote->getCustomerId();

        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(['accc' => 'amasty_company_account_customer'], [])
            ->join(
                ['acc' => 'amasty_company_account_company'],
                'accc.company_id = acc.company_id',
                ['reseller_id']
            )
            ->where('accc.customer_id = ?', $customerId);

        return $connection->fetchOne($select);
    }

    public function getSellerId($quoteId)
    {
        $quote = $this->quoteRepository->get($quoteId);
        $customerId = $quote->getCustomerId();

        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(['accc' => 'amasty_company_account_customer'], [])
            ->join(
                ['acc' => 'amasty_company_account_company'],
                'accc.company_id = acc.company_id',
                ['sales_representative_id']
            )
            ->where('accc.customer_id = ?', $customerId);

        $salesRepresentativeId = $connection->fetchOne($select);

        if($salesRepresentativeId)
        {
            $adminUser = $this->userFactory->create()->load($salesRepresentativeId);
            return $adminUser->getSellerId();
        }
        return '';

    }

    public function getQuoteItems($quoteId)
    {
        try {
            $quote = $this->quoteRepository->get($quoteId);
            return $quote->getAllItems();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return [];
        }
    }
}
