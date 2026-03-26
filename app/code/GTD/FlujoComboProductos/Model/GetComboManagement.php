<?php

/**
 * Copyright © hecho por balloon group juan reyes All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Empresas\FlujoComboProductos\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Empresas\FlujoComboProductos\Api\GetComboManagementInterface;
use Empresas\FlujoComboProductos\Helper\Data as Helper;
use Empresas\FlujoComboProductos\Api\CombosRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\SessionFactory as CheckoutSessionFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Empresas\PaqueteArmado\Model\PaqueteArmadoRepository;
use Empresas\PaqueteArmado\Model\ResourceModel\Item\CollectionFactory;

class GetComboManagement implements GetComboManagementInterface
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var CombosRepositoryInterface
     */
    private $combos;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var CheckoutSessionFactory
     */
    protected $checkoutSessionFactory;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var PaqueteArmadoRepository
     */
    protected $paqueteArmadoRepository;

    /**
     * @var CollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * Constructor
     *
     * @param Helper $helper
     * @param CombosRepositoryInterface $combos
     * @param CheckoutSession $checkoutSession
     * @param CheckoutSessionFactory $checkoutSessionFactory
     * @param CartRepositoryInterface $cartRepository
     * @param PaqueteArmadoRepository $paqueteArmadoRepository
     * @param CollectionFactory $itemCollectionFactory
     */
    public function __construct(
        Helper                    $helper,
        CombosRepositoryInterface $combos,
        CheckoutSession           $checkoutSession,
        CheckoutSessionFactory    $checkoutSessionFactory,
        CartRepositoryInterface   $cartRepository,
        PaqueteArmadoRepository $paqueteArmadoRepository,
        CollectionFactory $itemCollectionFactory
    ) {
        $this->helper = $helper;
        $this->combos = $combos;
        $this->checkoutSession = $checkoutSession;
        $this->checkoutSessionFactory = $checkoutSessionFactory;
        $this->cartRepository = $cartRepository;
        $this->paqueteArmadoRepository = $paqueteArmadoRepository;
        $this->itemCollectionFactory = $itemCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getComboApi($param)
    {

        if ($this->helper->isEnable() != 1) {
            throw new \InvalidArgumentException("functionality disabled");
        }


        $requiredFields = ['url_path'];
        foreach ($requiredFields as $field) {
            if (!isset($param[$field]) || empty($param[$field])) {
                throw new \InvalidArgumentException("$field is required and cannot be empty");
            }
        }

        $urls_params = $this->helper->getConfig('segmento_empresas_configuracion/url_combos/value');

        if ($urls_params) {
            $urls_paramsArray = unserialize($urls_params);
            $path = trim($param['url_path'], '/');
            foreach ($urls_paramsArray as $key => $value) {
                if ($path == $value['request_path']) {

                    $products = $this->getJsonComboProducts($value['combos']);

                    return [[
                        'success' => true,
                        'message' => $products['products_pack'],
                        'products_unavailable' => $products['products_unavailable'],
                    ]];
                }
            }
        }

        return [[
            'success' => false,
            'message' => __('row with ID "%1" does not exist.', $param['url_path'])
        ]];
    }


    public function getJsonComboProducts($id)
    {
        if (!$id) {
            return [];
        }

        if (strpos($id, 'paquete-armado-') === 0) {
            $id = substr($id, 1);
            $paquete = $this->paqueteArmadoRepository->get($id);
            $items = $this->itemCollectionFactory->create()->addFieldToFilter('parent_id', $id);
            return $this->helper->getPrepareJsonPaqueteArmado($items);
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
}
