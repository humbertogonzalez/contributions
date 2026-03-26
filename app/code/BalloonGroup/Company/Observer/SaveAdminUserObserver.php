<?php

declare(strict_types=1);

namespace BalloonGroup\Company\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveAdminUserObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $user = $observer->getEvent()->getObject();
        $data = $user->getData();
        $sellerId = $data['seller_id'] ?? '';

        if ($sellerId) {
            $user->setSellerId($sellerId);
        }
    }
}
