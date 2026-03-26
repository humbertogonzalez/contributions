<?php

namespace Redegal\CustomGoogleTagManager\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const COOKIE = 'cookie_gtm_id';
    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;
    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    private $subscriberFactory;
    /**
     * @var Session
     */
    private $session;

    public function __construct(
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Customer\Model\Session $session
    ) {
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager = $cookieManager;
        $this->subscriberFactory = $subscriberFactory;
        $this->session = $session;
    }

    /**
     * Guarda la informacion de GTM en la cookie
     * @param $data
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     * @author Sergio Ochoa Orozco <sergio.ochoa@redegal.com>
     */
    public function setCookieData($data)
    {
        $cookieMetaData = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $cookieMetaData->setDurationOneYear();
        $cookieMetaData->setPath('/');
        $cookieMetaData->setHttpOnly(true);
        $cookieMetaData->setSecure(true);
        $this->cookieManager->setPublicCookie(self::COOKIE, $data, $cookieMetaData);
    }

    /**
     * Elimina la informacion de la cookie
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     * @author Sergio Ochoa Orozco <sergio.ochoa@redegal.com>
     */
    public function removeCookieData()
    {
        $this->cookieManager->deleteCookie(self::COOKIE);
    }

    /**
     * Retorna la data de la Cookie
     * @return mixed|null
     * @author Sergio Ochoa Orozco <sergio.ochoa@redegal.com>
     */
    public function getCookieData()
    {
        $data = $this->cookieManager->getCookie(self::COOKIE);
        $dataJson = '';
        if ($data) {
            $dataJson = json_decode($data);
        }
        return $dataJson;
    }

    /**
     * Checkea que un customer este susbcrito al newsletter
     * @param $customerId
     * @return string
     * @author Sergio Ochoa Orozco <sergio.ochoa@redegal.com>
     */
    public function isCustomerSubscribeById($customerId)
    {
        $status = $this->subscriberFactory->create()->loadByCustomerId($customerId)->isSubscribed();

        if ($status == 1) {
            $status = 'Si';
        } else {
            $status = 'No';
        }

        return $status;
    }

    /**
     * Retorna Si o no si el customer esta logeado
     * @author Sergio Ochoa Orozco <sergio.ochoa@redegal.com>
     */
    public function isCustomerIsLogged()
    {
        $logged = 'No';
        if ($this->session->isLoggedIn()) {
            $logged = 'Si';
        }

        return $logged;
    }
}
