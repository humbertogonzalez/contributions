<?php

namespace Balloon\RestClientMapErrors\Controller\Adminhtml\Delete;

use Balloon\RestClientMapErrors\Api\Data\MapErrorInterface;
use Balloon\RestClientMapErrors\Model\MapErrorFactory;
use Balloon\RestClientMapErrors\Model\ResourceModel\MapError;
use Magento\Framework\App\Action\HttpDeleteActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Index implements HttpDeleteActionInterface
{

    public function __construct(
        protected readonly MapError $mapErrorResource,
        protected readonly MapErrorFactory $mapErrorFactory,
        protected readonly ResultFactory $resultFactory,
        protected readonly RequestInterface $request
    )
    {
    }

    public function execute(): ResultInterface
    {
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $dataResponse = [
            'success' => false,
            'body' => null
        ];
        try {
            $errorId = (int)$this->request->getParam('errorId');
            if (!$errorId) {
                $response->setHttpResponseCode(400);
                $dataResponse['body'] = 'El parametro errorId es obligatorio y de tipo entero';
            }
            if ($this->removeError($errorId)) {
                $dataResponse['success'] = true;
            } else {
                $dataResponse['body'] = 'Ocurrio un error inesperado, intente mas tarde';
            }
        } catch (\Exception $e) {
            $response->setHttpResponseCode(500);
            $dataResponse['body'] = $e->getMessage();
        }
        $response->setData($dataResponse);
        return $response;
    }

    protected function removeError($errorId): bool {
        $error = $this->mapErrorFactory->create();
        $this->mapErrorResource->load($error, $errorId, MapErrorInterface::ENTITY_ID);
        if ($error->getId()) {
            $this->mapErrorResource->delete($error);
            return true;
        }
        return false;
    }
}
