<?php
/**
 * Copyright © test All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace BalloonGroup\Distributes\Model\ResourceModel\Location;

use Magento\Store\Model\Store;


class Collection extends \Amasty\Storelocator\Model\ResourceModel\Location\Collection
{
    /**
     * Apply filters to locations collection
     *
     * @throws NoSuchEntityException
     */
    public function applyDefaultFilters()
    {
        $store = $this->storeManager->getStore(true)->getId();
        $attributesFromRequest = [];
        $productId = (int)$this->request->getParam('product');
        if (!$productId && $this->coreRegistry->registry('current_product')) {
            $productId = $this->coreRegistry->registry('current_product')->getId();
        }
        $categoryId = (int)$this->request->getParam('category');
        if (!$categoryId && $this->coreRegistry->registry('current_category')) {
            $categoryId = $this->coreRegistry->registry('current_category')->getId();
        }

        $select = $this->getSelect();
        if (!$this->storeManager->isSingleStoreMode()) {
            $this->addFilterByStores([Store::DEFAULT_STORE_ID, $store]);
        }
        $select->where('main_table.top = 1');
        $select->where('main_table.status = 1');
        $this->addDistance($select);

        $params = $this->request->getParams();
        if (isset($params['attributes'])) {
            $attributesFromRequest = $this->prepareRequestParams($params['attributes']);
        }
        $this->applyAttributeFilters($attributesFromRequest);

        if ($productId) {
            $this->filterLocationsByProduct($productId, $store);
        }
        if ($categoryId) {
            $this->filterLocationsByCategory($categoryId, $store);
        }
    }
}
