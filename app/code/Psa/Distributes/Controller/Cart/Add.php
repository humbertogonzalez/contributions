<?php

namespace Psa\Distributes\Controller\Cart;

use Amasty\Cart\Helper\Data;
use Amasty\Cart\Model\Cart\Add\GenerateResponse;
use Amasty\Cart\Model\Source\BlockType;
use Amasty\Cart\Model\Source\Option;
use Amasty\Cart\Model\Source\ConfirmPopup;
use Amasty\Cart\Model\Source\Section;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Helper\Cart;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Checkout\Model\Session;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ViewInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Url\Helper\Data as UrlHelper;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Customer\Model\Session as SessionCustomer;
use Psa\Distributes\Model\SerialFactory;
use Magento\Store\Model\StoreManagerInterface;

class Add extends \Magento\Checkout\Controller\Cart\Add
{
    public const AM_RECURRING_PAYMENTS_DISABLED = 'no';

    public const THEME_FOLDER= 'Amasty/';

    /**
     * @var string
     */
    protected string $type = Section::CART;

    /**
     * @var Product
     */
    private Product $product;

    /**
     * @var Data
     */
    protected Data $helper;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected \Magento\Catalog\Helper\Product $_productHelper;


    /**
     * @var LayoutFactory
     */
    protected LayoutFactory $layoutFactory;

    /**
     * @var ViewInterface
     */
    protected $_view;

    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * @var Registry
     */
    protected Registry $_coreRegistry;

    /**
     * @var UrlHelper
     */
    protected UrlHelper $urlHelper;

    /**
     * @var LayoutInterface
     */
    protected LayoutInterface $layout;

    /**
     * @var Escaper
     */
    protected Escaper $escaper;

    /**
     * @var Cart
     */
    protected Cart $cartHelper;

    /**
     * @var ResolverInterface
     */
    protected ResolverInterface $localeResolver;

    /**
     * @var ObjectFactory
     */
    private ObjectFactory $objectFactory;

    /**
     * @var string
     */
    private string $message = '';

    /**
     * @var ImageBuilder
     */
    private ImageBuilder $imageBuilder;

    /**
     * @var Configurable
     */
    private Configurable $configurable;

    /**
     * @var null|Product
     */
    private ?Product $quoteProduct = null;

    /**
     * @var FilterProvider
     */
    private FilterProvider $filterProvider;

    /**
     * @var BlockRepositoryInterface
     */
    private BlockRepositoryInterface $blockRepository;

    /**
     * @var GenerateResponse
     */
    private GenerateResponse $generateResponse;

    /**
     * Operates with magento design settings.
     *
     * @var DesignInterface
     */
    private DesignInterface $design;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        protected readonly SessionCustomer $session,
        protected readonly SerialFactory $serialFactory,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
        Data                                         $helper,
        \Magento\Catalog\Helper\Product              $productHelper,
        ResolverInterface                            $localeResolver,
        LayoutInterface                              $layout,
        PageFactory                                  $resultPageFactory,
        Registry                                     $coreRegistry,
        Escaper                                      $escaper,
        UrlHelper                                    $urlHelper,
        ObjectFactory                                $objectFactory,
        ImageBuilder                                 $imageBuilder,
        Configurable                                 $configurable,
        BlockRepositoryInterface    $blockRepository,
        FilterProvider   $filterProvider,
        GenerateResponse $generateResponse,
        DesignInterface                              $design
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $productRepository,

        );

        $this->helper = $helper;
        $this->_productHelper = $productHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->_view = $context->getView();
        $this->_coreRegistry = $coreRegistry;
        $this->urlHelper = $urlHelper;
        $this->layout = $layout;
        $this->escaper = $escaper;
        $this->localeResolver = $localeResolver;
        $this->objectFactory = $objectFactory;
        $this->imageBuilder = $imageBuilder;
        $this->configurable = $configurable;
        $this->filterProvider = $filterProvider;
        $this->blockRepository = $blockRepository;
        $this->generateResponse = $generateResponse;
        $this->design = $design;
    }

    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $message = __('We can\'t add this item to your shopping cart right now. Please reload the page.');
            return $this->addToCartResponse($message, 0);
        }

        $params = $this->getRequest()->getParams();
        
        $product = $this->_initProduct();

        /**
         * Check product availability
         */
        if (!$product) {
            $message = __('We can\'t add this item to your shopping cart right now.');
            return $this->addToCartResponse($message, 0);
        }
        $this->setProduct($product);
        $this->saveSku($product->getData()['sku']);
        try {
            if ($this->isShowOptionResponse($product, $params)) {
                return $this->showOptionsResponse($product);
            }

            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->localeResolver->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $cartModel = $this->getCartModel();
            $related = $this->getRequest()->getParam('related_product');
            $cartModel->addProduct($product, $params);
            if (!empty($related)) {
                $cartModel->addProductsByIds(explode(',', $related));
            }

            $cartModel->save();

            if ($product->getTypeId() === Configurable::TYPE_CODE && isset($params['super_attribute'])) {
                $this->setQuoteProduct($product);
                if ((bool)$this->helper->getModuleConfig('confirm_display/configurable_image')) {
                    $this->_coreRegistry->register(
                        'amasty_cart_conf_product',
                        $this->configurable->getProductByAttributes(
                            $params['super_attribute'],
                            $product
                        )
                    );
                } else {
                    $this->_coreRegistry->register('amasty_cart_conf_product', $product);
                }
            } else {
                $this->setQuoteProduct($product);
                $this->_coreRegistry->register('amasty_cart_conf_product', $product);
            }

            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );

            if (!$this->getCheckoutSession()->getNoCartRedirect(true)) {
                list($message, $productHasError) = $this->checkErrorMessages($product, $cartModel);

                if ($productHasError) {
                    return $this->showMessages($message);
                } else {
                    return $this->addToCartResponse($message, 1);
                }
            }
        } catch (LocalizedException $e) {
            return $this->showMessages(nl2br($this->escaper->escapeHtml($e->getMessage())));

        } catch (\Exception $e) {
            $message = __('We can\'t add this item to your shopping cart right now.');
            $message .= $e->getMessage();
            return $this->addToCartResponse($message, 0);
        }
    }

    private function checkErrorMessages(Product $product, CustomerCart $cartModel): array
    {
        $name = sprintf(
            '<a href="%s" title="%s">%s</a>',
            $product->getProductUrl(),
            $product->getName(),
            $product->getName()
        );
        $productHasError = false;
        $message = '';
        $quoteItem = $cartModel->getQuote()->getItemByProduct($product);

        if ($product->getTypeId() !== Grouped::TYPE_CODE
            && (!$quoteItem || $quoteItem->getErrorInfos())
        ) {
            $productHasError = true;
        } else {
            switch ($this->type) {
                case Section::QUOTE:
                    $message = __('%1 has been added to your quote cart', $name);
                    break;
                case Section::CART:
                default:
                    $message = __('%1 has been added to your cart', $name);
            }

            $message = $this->getProductAddedMessage($product, sprintf('<p>%s</p>', $message));
        }

        if ($cartModel->getQuote()->getHasError()) {
            $messages = [];

            foreach ($cartModel->getQuote()->getErrors() as $error) {
                $messages[] = sprintf('<p>%s</p>', $error->getText());
            }

            if ($productHasError) {
                $message .= implode($messages);
            } else {
                $message .= sprintf('<div class="message error">%s</div>', implode($messages));
            }
        }

        return [$message, $productHasError];
    }

    private function saveSku($sku) {
        $serie = $this->session->getSerie();
        if (!empty($serie)) {
            $baseSerial = $this->serialFactory->create();
            $baseSku = $baseSerial->load($sku,'sku')->getData();
            if (empty($baseSku['sku'])) {
                $data = $baseSerial->load($serie['code_serial'],'code_serial')->getData();
                $data['sku'] = $sku;
                $data['status'] = 1;
                $baseSerial->setData($data);
                $baseSerial->save();
            }
        }
        
    }

    /**
     * If product is composite - show popup with options
     * @param string $message
     * @return mixed
     */
    protected function showMessages($message)
    {
        $product = $this->getProduct();
        if (!$product->isComposite()) {
            return $this->addToCartResponse($message, 0);
        } else {
            $this->message = $message;
            return $this->showOptionsResponse($product);
        }
    }

    protected function isShowOptionResponse($product, $params)
    {
        $requiredOptions = $product->getTypeInstance()->hasRequiredOptions($product);
        $showOptionsResponse = false;
        switch ($product->getTypeId()) {
            case 'configurable':
                $attributesCount = $product->getTypeInstance()->getConfigurableAttributes($product)->count();
                $superParamsCount = (array_key_exists('super_attribute', $params)) ?
                    count(array_filter($params['super_attribute'])) : 0;
                if (isset($params['configurable-option'])) {
                    // compatibility with Amasty_Conf product matrix
                    $matrixSelected = false;
                    foreach ($params['amconfigurable-option'] as $amConfigurableOption) {
                        $optionData = $this->helper->decode($amConfigurableOption);
                        if (isset($optionData['qty']) && $optionData['qty'] > 0) {
                            $matrixSelected = true;
                            break;
                        }
                    }
                    if (!$matrixSelected) {
                        $this->messages[] = __('Please specify the quantity of product(s).');
                        $showOptionsResponse = true;
                    }
                } elseif ($attributesCount != $superParamsCount) {
                    $showOptionsResponse = true;
                }
                break;
            case 'grouped':
                if (!array_key_exists('super_group', $params)) {
                    $showOptionsResponse = true;
                }
                break;
            case 'amgiftcard':
                if (!array_key_exists('am_giftcard_recipient_email', $params)
                    && $product->getAmGiftcardType() != \Amasty\GiftCard\Model\Config\Source\GiftCardType::TYPE_PRINTED
                ) {
                    $showOptionsResponse = true;
                }
                break;
            case 'bundle':
                if (!array_key_exists('bundle_option', $params)) {
                    $showOptionsResponse = true;
                }
                break;
            case 'downloadable':
                if ($requiredOptions && !array_key_exists('links', $params) && !array_key_exists('options', $params)) {
                    $showOptionsResponse = true;
                }
                break;
            case 'simple':
            case 'virtual':
                // required custom options
                if ($requiredOptions && !array_key_exists('options', $params)) {
                    $showOptionsResponse = true;
                }
                break;
        }

        $amRecuringPayments = $product->getData('am_recurring_enable');

        if ($amRecuringPayments
            && $amRecuringPayments !== self::AM_RECURRING_PAYMENTS_DISABLED
            && !isset($params['subscribe'])
            && $this->helper->isRecurringPaymentsEnabled()
        ) {
            $showOptionsResponse = true;
        }

        if ($product->getData('am_available_for_wrapping')
            && $this->helper->isGiftWrapEnabled()
            && !isset($params['amwrap'])
        ) {
            $showOptionsResponse = true;
        }

        /* not required custom options block*/
        if (!$this->helper->isRedirectToProduct()
            && $product->getOptions()
            && $this->helper->getModuleConfig('dialog_popup/display_options') == Option::ALL_OPTIONS
            && !(array_key_exists('options', $params)
                || $this->isProductPageOrAjaxMini())
        ) {
            $showOptionsResponse = true;
        }

        $result = $this->objectFactory->create(['data' => ['show_options_response' => $showOptionsResponse]]);
        $this->_eventManager->dispatch(
            'amasty_cart_add_is_show_option_response_after',
            ['controller' => $this, 'result' => $result]
        );

        return $result->getShowOptionsResponse();
    }

    /**
     * @return bool
     */
    private function isMiniPage()
    {
        return $this->helper->getModuleConfig('dialog_popup/confirm_popup') == ConfirmPopup::MINI_PAGE;
    }

    /**
     * @return bool
     */
    private function isProductPageOrAjaxMini()
    {
        return $this->getRequest()->getParam('product_page') == 'true'
            || filter_var($this->getRequest()->getParam('requestAjaxMini'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Creating options popup
     * @param Product $product
     * @param string|null $submitRoute
     * @return mixed
     */
    protected function showOptionsResponse(Product $product, $submitRoute = null)
    {
        if ($this->helper->isRedirectToProduct()
            && $this->getRequest()->getParam('product_page') == "false"
        ) {
            $result['redirect'] = $product->getProductUrl();
            $resultObject = $this->objectFactory->create(['data' => ['result' => $result]]);
            $this->messageManager->addNoticeMessage(__('You need to choose options for your item.'));

            return $this->getResponse()->representJson(
                $this->helper->encode($resultObject->getResult())
            );
        }

        $this->_productHelper->initProduct($product->getEntityId(), $this);
        $page = $this->resultPageFactory->create(false, ['isIsolated' => false]);
        $page->addHandle('catalog_product_view');

        $type = $product->getTypeId();
        $page->addHandle('catalog_product_view_type_' . $type);

        $optionsHtml = $this->generateOptionsHtml($product, $page, $submitRoute);

        $isMiniPage = $this->helper->isRedirectToProduct() ? 1 : $this->isMiniPage();

        if ($isMiniPage) {
            $block = $page->getLayout()->createBlock(
                \Amasty\Cart\Block\Product\Minipage::class,
                'amasty.cart.minipage',
                [
                    'data' =>
                        [
                            'product'      => $product,
                            'optionsHtml'  => $optionsHtml,
                            'imageBuilder' => $this->imageBuilder,
                            'pageFactory'  => $this->resultPageFactory
                        ]
                ]
            );
            $message = $block->toHtml();
            $cancelTitle = __('Continue shopping');
        } else {
            $message = $optionsHtml;
            $cancelTitle = __('Cancel');
        }

        switch ($this->type) {
            case Section::QUOTE:
                $buttonTitle = __('Add to quote');
                break;
            case Section::CART:
            default:
                $buttonTitle = __('Add to cart');
        }

        $result = [
            'title'     =>  __('Set options'),
            'message'   =>  $message,
            'b2_name'   =>  $buttonTitle,
            'b1_name'   =>  $cancelTitle,
            'b2_action' =>  'amCartWidget.submitFormInPopup();',
            'b1_action' =>  'confirmHide();',
            'align' =>  'confirmHide();' ,
            'is_add_to_cart' =>  '0',
            'is_minipage' => $isMiniPage ? true : false
        ];

        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $result['selected_options'] = $this->getRequest()->getParam('super_attribute', null);
        }

        $resultObject = $this->objectFactory->create(['data' => ['result' => $result]]);
        $this->_eventManager->dispatch(
            'amasty_cart_add_show_option_response_after',
            ['controller' => $this, 'product' => $product, 'result' => $resultObject]
        );

        return $this->getResponse()->representJson(
            $this->helper->encode($resultObject->getResult())
        );
    }

    /**
     * @return bool
     */
    protected function isCurrentThemeJetTheme(): bool
    {
        $themePath = $this->design->getDesignTheme()->getThemePath();
        if ($themePath) {
            return strpos($this->design->getDesignTheme()->getThemePath(), self::THEME_FOLDER) !== false;
        }

        return false;
    }

    /**
     * Generate html for product options
     * @param Product $product
     * @param $page
     * @param string|null $submitRoute
     *
     * @return mixed|string
     */
    protected function generateOptionsHtml(Product $product, $page, $submitRoute)
    {
        if ($this->isCurrentThemeJetTheme()) {
            $amGiftWrapBlock = $page->getLayout()->getBlock('amgiftwrap.product.view');
            $amGiftWrapBlockAdditional = $page->getLayout()->getBlock('amgiftwrap.product.view.additional');

            if ($amGiftWrapBlock && $amGiftWrapBlockAdditional) {
                $amGiftWrapBlock->setTemplate('');
            }
        }

        $html = $this->getOptionsHtml($page, $product, $submitRoute);

        $html = str_replace(
            '"spConfig',
            '"priceHolderSelector": ".price-box[data-product-id=' . $product->getId() . ']", "spConfig',
            $html
        );

        if ($submitRoute === \Amasty\Cart\Controller\Wishlist\Cart::WISHLIST_URL) {
            $html = str_replace(
                '</form>',
                '<input name="item" type="hidden" value="'
                            . (int)$this->getRequest()->getParam('item') . '"></form>',
                $html
            );
        }

        $contentClass = 'product-options-bottom';
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $contentClass .= ' product-item';
        }

        $errors = '';
        if ($this->message) {
            $errors .= '<div class="message error">' . $this->message . '</div>';
        }

        $isMiniPage = $this->helper->isRedirectToProduct() ? 1 : $this->isMiniPage();

        if ($isMiniPage) {
            $title = '';
        } else {
            $title = sprintf(
                '<a href="%s" title="%s" class="added-item">%s</a>',
                $product->getProductUrl(),
                $product->getName(),
                $product->getName()
            );
        }

        $html = $this->wrapOptionsHtml($html, $contentClass, $title, $errors);
        $html = $this->replaceHtmlElements($html, $product);

        return $html;
    }

    /**
     * @see \Amasty\AjaxCartHyva\Plugin\Cart\Controller\Cart\Add\ReplaceOptionsHtml
     */
    public function getOptionsHtml(Page $page, Product $product, ?string $submitRoute): string
    {
        $block = $page->getLayout()->getBlock('product.info');
        if (!$block) {
            $block = $page->getLayout()->createBlock(
                \Magento\Catalog\Block\Product\View::class,
                'product.info',
                [ 'data' => [] ]
            );
        }

        $block->setProduct($product);
        if ($submitRoute) {
            $block->setData('submit_route_data', [
                'route' => $submitRoute
            ]);
        }

        return $block->toHtml();
    }

    /**
     * @see \Amasty\AjaxCartHyva\Plugin\Cart\Controller\Cart\Add\ModifyOptionsWrap
     */
    public function wrapOptionsHtml(string $optionsHtml, string $contentClass, string $title, string $errors): string
    {
        return '<div class="' . $contentClass . '" >'
            . $title
            . $errors
            . $optionsHtml
            . '</div>';
    }

    /**
     * @param Product $product
     * @param $message
     * @return string
     */
    protected function getProductAddedMessage(Product $product, $message)
    {
        if ($this->helper->isDisplayImageBlock()) {
            $block = $this->layout->getBlock('amasty.cart.product');
            if (!$block) {
                $block = $this->layout->createBlock(
                    \Amasty\Cart\Block\Product::class,
                    'amasty.cart.product',
                    ['data' => ['cart_type' => $this->type]]
                );
                $block->setTemplate('Amasty_Cart::dialog.phtml');
            }

            $block->setQtyHtml($this->getQtyBlockHtml());
            $block->setProduct($product);

            $message = $block->toHtml();
        } else {
            $message .= $this->getQtyBlockHtml();
        }

        $type = $this->helper->getModuleConfig('selling/block_type');
        if ($type && $type !== '0') {
            /* replace uenc for correct redirect*/
            $refererUrl = $this->_request->getServer('HTTP_REFERER');
            $message = $this->replaceUenc($refererUrl, $message);
        }

        return $message;
    }

    /**
     * @param $message
     * @param $result
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function makeResponse($message, $result)
    {
        $result['related'] = $this->getAdditionalBlockHtml();
        $this->generateResponse->setType($this->type);
        $this->generateResponse->setCartModel($this->getCartModel());

        return $this->generateResponse->execute($message, $result);
    }

    /**
     * @param $message
     * @param $status
     * @param array $additionalResult
     * @return mixed
     */
    protected function addToCartResponse($message, $status, $additionalResult = [])
    {
        $result = ['is_add_to_cart' => $status];
        if (!$status) {
            $message = '<div class="message error">' . $message . '</div>';
            $result = $this->makeResponse($message, $result);
        }

        if (!$this->helper->isOpenMinicart() && $status) {
            $result = $this->makeResponse($message, $result);
        }

        $result = array_merge($result, $additionalResult);

        if ($status) {
            $result['product_sku'] = $this->getProduct()->getSku();
            $result['product_id'] = $this->getProduct()->getId();
        }

        $resultObject = $this->objectFactory->create(['data' => ['result' => $result]]);
        $this->_eventManager->dispatch(
            'amasty_cart_add_addtocart_response_after',
            ['controller' => $this, 'result' => $resultObject]
        );

        return $this->getResponse()->representJson(
            $this->helper->encode($resultObject->getResult())
        );
    }

    /**
     * @return string
     */
    protected function getAdditionalBlockHtml()
    {
        $type = $this->helper->getModuleConfig('selling/block_type');
        $html = '';
        $this->layout->createBlock(\Magento\Framework\View\Element\FormKey::class, 'formkey');
        switch ($type) {
            case BlockType::CMS_BLOCK:
                $html = $this->getCmsBlockHtml();
                break;
            case BlockType::RELATED:
            case BlockType::CROSSSELL:
                //display related products
                $html = $this->getProductsHtml($type);
                break;
        }
        $html = preg_replace(
            '@\[data-role=swatch-option-(\d+)]@',
            '#confirmBox [data-role=swatch-option-$1]',
            $html
        );

        return $html;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws LocalizedException
     */
    private function getCmsBlockHtml()
    {
        $html = '';
        if ($blockId = $this->helper->getCmsBlockId()) {
            $storeId = $this->_storeManager->getStore()->getId();
            /** @var \Magento\Cms\Model\Block $block */
            $block = $this->blockRepository->getById($blockId);
            if ($block->isActive()) {
                $html = $this->filterProvider->getBlockFilter()->setStoreId($storeId)->filter(
                    $block->getContent()
                );
            }
        }

        return $html;
    }

    /**
     * @param $type
     * @return string
     */
    private function getProductsHtml($type)
    {
        $html = '';
        $product = $this->getProduct();
        if ($product) {
            $this->_productHelper->initProduct($product->getEntityId(), $this);
            $this->layout->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                ['data' => [
                    'price_render_handle' => 'catalog_product_prices',
                    'use_link_for_as_low_as' => true
                ]]
            );
            $blockName = 'Amasty\Cart\Block\Product\\';
            if ($this->helper->isTargetRuleEnabled()) {
                $blockName .= 'TargetRule\\';
            }
            $block = $this->layout->createBlock(
                $blockName . ucfirst($type),
                'amasty.cart.product_' . $type,
                ['data' => ['cart_type' => $this->type]]
            );
            $block->setProduct($product)->setTemplate("Amasty_Cart::product/list/items.phtml");
            $html = $block->toHtml();
            $refererUrl = $product->getProductUrl();
            $html = $this->replaceUenc($refererUrl, $html);
        }

        return $html;
    }

    /**
     * @param mixed $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param string $refererUrl
     * @param string $item
     * @return string mixed
     */
    private function replaceUenc($refererUrl, $item)
    {
        $currentUenc = $this->urlHelper->getEncodedUrl();
        $newUenc = $this->urlHelper->getEncodedUrl($refererUrl);
        return str_replace($currentUenc, $newUenc, $item);
    }

    /**
     * @return string
     */
    private function getQtyBlockHtml()
    {
        $result = '';
        // if quote product not detected (example: Amasty_Conf matrix used) qty block not displayed
        if ($this->helper->isChangeQty() && $this->getQuoteProduct()) {
            // use quote getItemByProduct function for avoid getting wrong quote item in case
            // with configurable simple with different custom options
            $quoteItem = $this->getCartModel()->getQuote()->getItemByProduct($this->getQuoteProduct());
            if ($quoteItem) {
                $block = $this->layout->getBlock('amasty.cart.qty');
                if (!$block) {
                    $block = $this->layout->createBlock(
                        \Amasty\Cart\Block\Product::class,
                        'amasty.cart.qty',
                        ['data' => []]
                    );
                }
                $quoteItem = $quoteItem->getParentItem() ?: $quoteItem;

                $block->setTemplate('Amasty_Cart::qty.phtml');
                $block->setQty($quoteItem->getQty());
                $quoteItemId = $quoteItem->getData('parent_item_id') ?: $quoteItem->getData('item_id');
                $block->setQuoteItemId($quoteItemId);

                $result = $block->toHtml();
            }
        }

        return $result;
    }

    private function replaceHtmlElements($html, $product)
    {
        /* replace uenc for correct redirect*/
        $currentUenc = $this->urlHelper->getEncodedUrl();
        $refererUrl = $product->getProductUrl();
        $newUenc = $this->urlHelper->getEncodedUrl($refererUrl);

        $html = str_replace($currentUenc, $newUenc, $html);
        $html = str_replace('"swatch-opt"', '"swatch-opt swatch-opt-' . $product->getId() . '"', $html);
        $html = str_replace('spConfig": {"attributes', 'spConfig": {"containerId":"#confirmBox", "attributes', $html);
        $html = str_replace('[data-role=swatch-options]', '#confirmBox [data-role=swatch-options]', $html);

        return $html;
    }

    /**
     * @return Session
     */
    public function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * @return CustomerCart
     */
    public function getCartModel()
    {
        return $this->cart;
    }

    /**
     * @param Product $product
     */
    private function setQuoteProduct($product)
    {
        $this->quoteProduct = $product;
    }

    /**
     * @return Product|null
     */
    private function getQuoteProduct()
    {
        return $this->quoteProduct;
    }
}
