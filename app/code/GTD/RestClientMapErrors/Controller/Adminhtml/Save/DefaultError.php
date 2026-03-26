<?php

namespace Balloon\RestClientMapErrors\Controller\Adminhtml\Save;

use Balloon\RestClientMapErrors\Api\Data\DefaultErrorInterface;
use Balloon\RestClientMapErrors\Api\Data\MapErrorInterface;
use Balloon\RestClientMapErrors\Api\Data\TypeMapErrorInterface;
use Balloon\RestClientMapErrors\Model\DefaultErrorFactory;
use Balloon\RestClientMapErrors\Model\ResourceModel\DefaultError as DefaultErrorResource;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class DefaultError implements HttpPostActionInterface
{
    public function __construct(
        protected readonly DefaultErrorResource $defaultErrorResource,
        protected readonly DefaultErrorFactory $defaultErrorFactory,
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
            $error = $this->request->getParam('error');
            if (
                !isset($error[MapErrorInterface::BLOCK_CODE]) ||
                !isset($error[DefaultErrorInterface::ENTITY_ID])
            ) {
                $response->setHttpResponseCode(400);
                $dataResponse['body'] = 'Parametro inválido';
            }
            if ($newError = $this->update($error)) {
                $dataResponse['success'] = true;
                $dataResponse['body'] = $newError;
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

    protected function update(array $errorData): DefaultErrorInterface {
        $error = $this->defaultErrorFactory->create();
        $this->defaultErrorResource->load($error, $errorData[DefaultErrorInterface::ENTITY_ID], DefaultErrorInterface::ENTITY_ID);
        $error->setData(DefaultErrorInterface::BLOCK_CODE, $errorData[DefaultErrorInterface::BLOCK_CODE]);
        $this->defaultErrorResource->save($error);
        return $error;
    }
}
