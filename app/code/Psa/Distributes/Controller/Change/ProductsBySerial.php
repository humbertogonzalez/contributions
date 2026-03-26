<?php

namespace Psa\Distributes\Controller\Change;

use Amasty\Storelocator\Model\LocationFactory;
use Amasty\Storelocator\Model\ResourceModel\Location as LocationResource;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;
use Psa\Distributes\Helper\Data;
use Psa\Distributes\Model\SerialFactory;
use Psa\Distributes\Model\ResourceModel\Serial;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Psr\Log\LoggerInterface;

class ProductsBySerial implements HttpGetActionInterface
{
    /**
     * ProductsBySerial constructor
     *
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     * @param Data $dataService
     * @param ProductRepositoryInterface $productRepository
     * @param CollectionFactory $productCollectionFactory
     * @param LayoutInterface $layout
     * @param LocationFactory $locationFactory
     * @param SerialFactory $serialFactory
     * @param Serial $serial
     * @param Session $session
     * @param LocationResource $locationResource
     * @param StoreManagerInterface $storeManager
     * @param CheckoutSession $checkoutSession
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected readonly JsonFactory $resultJsonFactory,
        protected readonly RequestInterface $request,
        protected readonly Data $dataService,
        protected readonly ProductRepositoryInterface $productRepository,
        protected readonly CollectionFactory $productCollectionFactory,
        protected readonly LayoutInterface $layout,
        protected readonly LocationFactory $locationFactory,
        protected readonly SerialFactory $serialFactory,
        protected readonly Serial $serial,
        protected readonly Session $session,
        protected readonly LocationResource $locationResource,
        protected readonly StoreManagerInterface $storeManager,
        protected CheckoutSession $checkoutSession,
        protected LoggerInterface $logger
    ) {
    }
    public function execute(): Json
    {
        $this->logger->info("===== ProductsBySerial::execute =====");
        $resultJson = $this->resultJsonFactory->create();
        $serial = $this->request->getParam('serial');
        if (!$serial) {
            $resultJson->setData('El parametro serial es obligatorio');
            return $resultJson->setHttpResponseCode(400);
        }
        try {
            $resultJson->setData($this->getCanje($serial));
            $resultJson->setHttpResponseCode(200);
        } catch (\Exception $e) {
            $resultJson->setData($e->getMessage());
            $resultJson->setHttpResponseCode(500);
        }
        return $resultJson;
    }

    protected function getDipIdByCode(string $code): ?int {
        $location = $this->locationFactory->create();
        $this->locationResource->load($location, $code, 'code');
        if ($location->getId()) {
            return $location->getId();
        }
        return false;
    }
    public function getCanje($serial): array
    {
        $this->logger->info("===== ProductsBySerial::getCanje =====");
        $data['resultDistributor'] = false;
        $dealer = $this->dataService->dipNumberSerie($serial);
        $data['dipId'] =0;
        $data['codeWODistributor'] = $this->dataService->getConfig('distributes/textEdit/withoutDealer');
        $data['resultWOProduct'] = $this->dataService->getConfig('distributes/textEdit/withoutSerial');
        if(!empty($dealer)) {
            $this->logger->info("> API dipNumberSerie con data... " . $serial);
            $dip = $this->dataService->verifyVar($dealer,'dip');
            $codeDist = $this->dataService->verifyVar($dip,'codigo');
            if ($dip && $codeDist) {
                $data['codeDist'] = $codeDist;
                $data['dipId'] = $this->getDipIdByCode($codeDist);
                $data['nombre'] = $dip->denominacion;
                $data['telefono'] = $dip->telefonoCelular;
                $data['email'] = $dip->email;
                $data['codeWODistributor'] = '';
                $data['resultWOProduct'] = '';
                $data['resultDistributor'] = true;
            }
        }else {
            $this->logger->info("> API dipNumberSerie vacía... " . $serial);
            $data['codeWODistributor'] = $this->dataService->getConfig('distributes/textEdit/withoutDealer');
        }
        $data['resultProduct'] = false;
        $dataProduct = $this->dataService->productNumberSerie($serial);
        if(!empty($dataProduct)) {
            $this->logger->info("> API productNumberSerie con data... " . $serial);
            $codeSKU = $this->dataService->verifyVar($dataProduct,'unidad');
            $productSKU = $this->dataService->verifyVar($codeSKU,'producto');
            $codeDist = $this->dataService->verifyVar($productSKU,'codigo');

            $this->logger->info(print_r($codeSKU, true));
            $this->logger->info(print_r($productSKU, true));
            $this->logger->info("> CodeDist: " . $codeDist);

            if (
                !empty($dataProduct) &&
                $codeSKU &&
                $productSKU &&
                $codeDist
            ) {
                $this->logger->info("> Aplicar Descuento");
                $this->session->setCanje('canje');

                $data['resultWOProduct'] = '';
                $data['codeProduct'] = $codeDist;
                $data['fechaVencimiento'] = $this->dataService->verifyVar($codeSKU,'fechaVencimiento');
                $data['fecha'] = !$data['fechaVencimiento'] ? 'No registra':substr($data['fechaVencimiento'],0,10);
                $data['descripcion'] = $this->dataService->verifyVar($productSKU,'descripcion');
                $data['denominacion'] = $this->dataService->verifyVar($productSKU,'denominacion');
                $data['resultProduct'] = true;
                $mnsSerial = $this->saveSerial($serial);
                $this->logger->info(print_r($mnsSerial, true));
                $this->checkoutSession->setCanje('canje');
                /*if ($mnsSerial['status'] != 3) {
                    $data['resultWOProduct'] = '';
                    $data['codeProduct'] = $codeDist;
                    $data['fechaVencimiento'] = $this->dataService->verifyVar($codeSKU,'fechaVencimiento');
                    $data['fecha'] = !$data['fechaVencimiento'] ? 'No registra':substr($data['fechaVencimiento'],0,10);
                    $data['descripcion'] = $this->dataService->verifyVar($productSKU,'descripcion');
                    $data['denominacion'] = $this->dataService->verifyVar($productSKU,'denominacion');
                    $data['resultProduct'] = true;
                }
                else {
                    $data['resultProduct'] = false;
                    $data['resultWOProduct'] = 'Esta Serie ya fue utilizada';
                }*/

            }
        }else {
            $this->logger->info("> API productNumberSerie vacía... " . $serial);
            $this->logger->info("> NO Aplicar Descuento");
            $data['resultProduct'] = false;
            //$data['resultWOProduct'] = 'Esta Serie ya fue utilizada';
            $data['resultWOProduct'] = $this->dataService->getConfig('distributes/textEdit/withoutSerial');
        }

        return $this->getDataCanje($data);

    }

    private function saveSerial($serial) {
        $this->logger->info("===== ProductsBySerial::saveSerial =====");
        $baseSerial = $this->serialFactory->create();
        $dataSerial=$baseSerial->load($serial,'code_serial')->getData();
        if (empty($dataSerial)) {
            $data['code_serial'] = $serial;
            $baseSerial->setData($data);
            $baseSerial->save();
            $dataSerial = $this->serialFactory->create()->load($serial,'code_serial')->getData();
        }

        $this->session->setSerie($dataSerial);
        return $dataSerial;

    }

    protected function getDataCanje($data): array
    {
        if ($data['resultProduct']) {
            $product = $this->productRepository->get($data['codeProduct']);
            $canjeProducts = $product->getSkusCanje();
            $data['products'] = [];
            if (empty($canjeProducts)) {
                $data['resultProduct'] = false;
                return $data;
            }
            if (empty($data['dipId'])) {
                $data['dipId'] = 0;
            }
            $productCollection = $this->productCollectionFactory->create();
            $productCollection->addAttributeToSelect('*');
            $productCollection->addFieldToFilter('status', Status::STATUS_ENABLED);
            $productCollection->addFieldToFilter('sku', ['in' => $canjeProducts]);
            foreach ($productCollection->getItems() as $key => $product) {
                $data['products'][] = $product->getData();
                $data['products'][sizeof($data['products']) -1]['url'] = $this->getUrl($product->getUrlKey(), $data['dipId']);
                $data['products'][sizeof($data['products']) -1]['price'] = $this->getPrice(
                    $product
                );
                $data['products'][sizeof($data['products']) -1]['image'] = $product->getImage() ?? $this->dataService->getConfig('catalog/placeholder/image_placeholder');
            }

        }
        return $data;
    }

    protected function getUrl(string $urlKey, int $dipId): string
    {
        return $this->storeManager->getStore()->getBaseUrl() . $urlKey . ".html?canje=1&dipId={$dipId}";
    }

    public function getPrice(\Magento\Catalog\Model\Product $product)
    {
        $priceRender = $this->layout->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->layout->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                [
                    'display_minimal_price'  => true,
                    'use_link_for_as_low_as' => true,
                    'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
                ]
            );
        }

        return $price;
    }

}
