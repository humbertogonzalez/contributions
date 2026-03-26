<?php

/**
 * Copyright © hecho por balloon group juan reyes All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Empresas\FlujoComboProductos\Block\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\View\Element\Template\Context;
use Empresas\FlujoComboProductos\Helper\Data as Helper;
use Magento\Framework\View\Element\Template;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\App\Request\Http;
use Empresas\FlujoComboProductos\Api\CombosRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Checkout\Model\SessionFactory as CheckoutSessionFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DataObjectFactory;
use Empresas\Datalayers\Api\DatalayersRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Empresas\CurrencySymbol\Helper\Data as HelperSymbol;
use Empresas\CustomHeaderLogo\Helper\Data as HelperLogo;

class Index extends Template
{

    protected $helper;
    protected $blockFactory;
    protected $request;
    protected $combos;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;
    protected $checkoutSessionFactory;

    protected $guestCartManagement;
    protected $guestCartRepository;
    protected $cartRepository;
    protected $storeManager;
    protected $objectFactory;
    protected $productRepository;
    protected $helperLogo;

    /**
     * @var DatalayersRepositoryInterface
     */
    protected $datalayers;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var SortOrderBuilder
     */
    protected $helperSymbol;

    /**
     * Constructor
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context                       $context,
        Helper                        $helper,
        BlockFactory                  $blockFactory,
        Http                          $request,
        CombosRepositoryInterface     $combos,
        QuoteFactory                  $quoteFactory,
        CartManagementInterface       $cartManagement,
        CheckoutSession               $checkoutSession,
        CheckoutSessionFactory        $checkoutSessionFactory,
        GuestCartManagementInterface  $guestCartManagement,
        GuestCartRepositoryInterface  $guestCartRepository,
        CartRepositoryInterface       $cartRepository,
        StoreManagerInterface         $storeManager,
        DataObjectFactory             $objectFactory,
        ProductRepositoryInterface    $productRepository,
        DatalayersRepositoryInterface $datalayers,
        SearchCriteriaBuilder         $searchCriteriaBuilder,
        SortOrderBuilder              $sortOrderBuilder,
        HelperSymbol                  $helperSymbol,
        HelperLogo                    $helperLogo,
        array                         $data = []
    ) {
        $this->helper = $helper;
        $this->blockFactory = $blockFactory;
        $this->request = $request;
        $this->combos = $combos;
        $this->quoteFactory = $quoteFactory;
        $this->cartManagement = $cartManagement;
        $this->checkoutSession = $checkoutSession;
        $this->checkoutSessionFactory = $checkoutSessionFactory;
        $this->guestCartManagement = $guestCartManagement;
        $this->guestCartRepository = $guestCartRepository;
        $this->cartRepository = $cartRepository;
        $this->storeManager = $storeManager;
        $this->objectFactory = $objectFactory;
        $this->productRepository = $productRepository;
        $this->datalayers = $datalayers;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->helperSymbol = $helperSymbol;
        $this->helperLogo = $helperLogo;
        parent::__construct($context, $data);
    }

    public function getParentJsLayout(): string
    {
        return parent::getJsLayout();
    }

    public function getJsLayout()
    {

        $layout = json_decode(parent::getJsLayout(), true);
        $layout['components']['landing_content']['url_params'] = $this->request->getParams() ?? '';
        return json_encode($layout);
    }

    public function getJsonComboProducts()
    {
        $id = $this->request->getParam('combo_id');
        if (!$id) {
            return [];
        }
        $combo = $this->getComboById($id);
        return $this->helper->getPrepareJsonProducts($combo);
    }

    public function getComboById($id)
    {
        return $this->combos->get($id);
    }

    public function getQuoteId()
    {
        $session = $this->checkoutSession->getQuote()->getId();
        if (!$session) {
            $session = $this->checkoutSessionFactory->create();
            $quote = $session->getQuote();
            $this->cartRepository->save($quote);
            $session->replaceQuote($quote)->unsLastRealOrderId();
            return $quote->getId();
        } else {
            return $session;
        }
    }

    public function getDatalayers()
    {
        $options = [];
        $sortOrder = $this->sortOrderBuilder->setField('paso')->setDirection('ASC')->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('producto_combo', $this->request->getParam('combo_id'), 'eq')
            ->setSortOrders([$sortOrder])
            ->create();
        $datalayersitems = $this->datalayers->getList($searchCriteria)->getItems();

        foreach ($datalayersitems as $item) {
            $options[] = [
                "datalayers_id" => $item->getDatalayersId(),
                "paso" => $item->getPaso(),
                "event" => $item->getEvent(),
                "page" => $item->getPage(),
                "pagename" => $item->getPagename(),
                "idplan" => $item->getIdplan(),
                "producto_combo" => $item->getProductoCombo(),
                "type" => $item->getType(),
            ];
        }

        return $options;
    }

    public function getLogoRedirection()
    {
        if (!$this->helperLogo->isEnable()) {
            return null;
        }

        return [
            'url' => $this->helperLogo->getUrl(),
            'redirection_type' => $this->helperLogo->getTypeRedirect()
        ];
    }

    public function getVariablesEntorno()
    {
        $symbol = $this->helperSymbol->getSymbolCurrency('request_path', $this->request->getParam('request_path'));
        $currencySymbol = $symbol['symbol'];
        return [
            'currency_symbol' => $currencySymbol,
            'config_module' => [
                'active' => $this->helper->isEnable()
            ],
            'quote_id' => $this->getQuoteId(),
            'store' => [
                'id' => $this->storeManager->getStore()->getId(),
                'name' => $this->helper->getStore()->getName(),
                'code' => $this->helper->getStore()->getCode()
            ],
            'combo' => [
                'tipo_producto' => $this->request->getParam('tipo_producto') ? $this->request->getParam('tipo_producto') : '',
            ],
            'base_url' => $this->storeManager->getStore()->getBaseUrl(),
            'datalayers' => $this->getDatalayers(),
            'cart_rule' => $this->helper->getCartRule($this->request->getParam('request_path')),
            'logo_redirection' => $this->getLogoRedirection()
        ];
    }
}
