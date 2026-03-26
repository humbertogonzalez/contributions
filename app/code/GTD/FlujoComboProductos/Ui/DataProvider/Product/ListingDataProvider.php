<?php
namespace Empresas\FlujoComboProductos\Ui\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Catalog\Model\ProductFactory;
use Magento\Store\Model\StoreManagerInterface;

class ListingDataProvider extends AbstractDataProvider
{
    protected $collection;
    protected $request;
    protected $productFactory;
    protected $storeManager;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        ProductFactory $productFactory,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    )
    {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->request = $request;
        $this->productFactory = $productFactory;
        $this->storeManager = $storeManager;
        $this->initializeCollection();
    }

    protected function initializeCollection()
    {
        $this->collection->addAttributeToSelect('*');

        $this->collection->joinField(
            'websites',
            'catalog_product_website',
            'website_id',
            'product_id=entity_id',
            null,
            'left'
        );
        $this->collection->groupByAttribute('entity_id');
    }

    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        $items = $this->getCollection()->toArray();
        $data = [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => [],
        ];

        $processedIds = [];

        foreach ($items as $item) {
            if (!isset($processedIds[$item['entity_id']])) {
                $product = $this->productFactory->create()->load($item['entity_id']);
                $websiteIds = $product->getWebsiteIds();
                $item['websites'] = [];
                foreach ($websiteIds as $websiteId) {
                    $item['websites'][] = $this->storeManager->getWebsite($websiteId)->getName();

                }
                $data['items'][] = $item;
                $processedIds[$item['entity_id']] = true;
            }
        }





        return $data;
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() == 'store_id') {
            $this->collection->addStoreFilter($filter->getValue());
        } elseif ($filter->getField() == 'fulltext') {
            $value = $filter->getValue();
            $this->collection->addAttributeToFilter(
                [
                    ['attribute' => 'name', 'like' => "%$value%"],
                    ['attribute' => 'sku', 'like' => "%$value%"],
                ],
                '',
                'left'
            );
        } else {
            $this->collection->addFieldToFilter(
                $filter->getField(),
                [$filter->getConditionType() => $filter->getValue()]
            );
        }
    }
}
