<?php


namespace Redegal\Sepomex\Controller\Ajax;

class Get extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    protected $jsonHelper;
    protected $_sepomexFactory;

    const ZIP_CODE_FOUND = "found";
    const ZIP_CODE_NOT_FOUND = "not found";

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Redegal\Sepomex\Model\SepomexFactory $sepomexFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->_sepomexFactory = $sepomexFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
	public function execute()
	{
		$cp = $this->getRequest()->getParam('cp');
		$neighborhoods = $this->_sepomexFactory->create()
		->getCollection()
		->addFieldToFilter('d_codigo', $cp)
		->setOrder('d_asenta','ASC');

		if ($neighborhoods->getSize() > 0) {
			try {
				$response["neighborhood"] = $neighborhoods->getData();
				$response["status"] = self::ZIP_CODE_FOUND;
				return $this->jsonResponse($response);
			} catch (\Magento\Framework\Exception\LocalizedException $e) {
				return $this->jsonResponse($e->getMessage());
			} catch (\Exception $e) {
				$this->logger->critical($e);
				return $this->jsonResponse($e->getMessage());
			}
		} else {
			$noZipCode = ["status" => self::ZIP_CODE_NOT_FOUND, "response" => "No hay colonias con ese zipcode"];
			return $this->jsonResponse($noZipCode);
		}
	}

    /**
     * Create json response
     *
     * @param string $response
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }
}
